<?php

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', realpath(dirname(__FILE__) . DS . '..' . DS));
define('MAIL_DIR', 'mail');
define('BODY_DIR', 'body');
define('WWW_PATH', BASE_PATH . DS . 'www');
define('MAIL_PATH', WWW_PATH . DS . MAIL_DIR);
define('BODY_PATH', MAIL_PATH . DS . BODY_DIR);
define('AMQP_HOSTNAME', 'localhost');
define('AMQP_LOGIN', 'user');
define('AMQP_PASSWORD', '1q2w3e');
define('AMQP_PORT', '5672');
define('AMQP_VHOST', 'binatex');
define('EXCHANGE_NAME', 'postmanq');
define('EXCHANGE_TYPE', AMQP_EX_TYPE_FANOUT);
define('ROUTING_KEY', '');
define('QUEUE_NAME', 'postmanq');
define('MAX_FILES', 10);

require_once BASE_PATH . '/vendor/autoload.php';

function checkDir($dirname) {
    if (!is_dir($dirname)) {
        mkdir($dirname, 0777, true);
    }
}

function render($filename, $tmpl, $params) {
    ob_start();
    extract($params);

    include $tmpl;

    $out = ob_get_contents();
    ob_end_clean();

    file_put_contents($filename, $out);
}

checkDir(WWW_PATH);
checkDir(MAIL_PATH);
checkDir(BODY_PATH);

$conn = new AMQPConnection();
$conn->setHost(AMQP_HOSTNAME);
$conn->setLogin(AMQP_LOGIN);
$conn->setPassword(AMQP_PASSWORD);
$conn->setPort(AMQP_PORT);
$conn->setVhost(AMQP_VHOST);
$conn->connect();

if (!$conn->isConnected()) {
    exit('cant \'t connect to amqp server');
}

$chan = new AMQPChannel($conn);

try {
    $exchange = new AMQPExchange($chan);
    $exchange->setType(EXCHANGE_TYPE);
    $exchange->setName(EXCHANGE_NAME);
    $exchange->setFlags(AMQP_DURABLE);
    $exchange->declareExchange();

    $queue = new AMQPQueue($chan);
    $queue->setFlags(AMQP_DURABLE);
    $queue->setName(QUEUE_NAME);
    $queue->declareQueue();

    $queue->bind(EXCHANGE_NAME,$queue->getName());

    for ($i = 0;$i < MAX_FILES;$i++) {
        $message = $queue->get(AMQP_AUTOACK);
        if ($message instanceof AMQPEnvelope) {
            $json = json_decode($message->getBody());

            $parser = new PhpMimeMailParser\Parser();
            $parser->setText($json->body);

            $subject = $parser->getHeader('subject');
            $from = htmlspecialchars($parser->getHeader('from'));
            $to = htmlspecialchars($parser->getHeader('to'));
            $date = $parser->getHeader('date');
            $body = $parser->getMessageBody('html');

            $filename = md5($subject . $from . $to . round(microtime(true) * 1000) . rand(0, 999999)) . '.html';

            $body_path = BODY_PATH . DS . $filename;
            file_put_contents($body_path, $body);

            render(
                MAIL_PATH . DS . $filename,
                BASE_PATH . DS . 'tmpls' . DS . 'mail.php',
                [
                    'subject' => $subject,
                    'from' => $from,
                    'to' => $to,
                    'date' => $date,
                    'body' => BODY_DIR.DS.$filename,
                ]
            );
        } else {
            break;
        }
    }

    $files = [];
    foreach (scandir(MAIL_PATH) as $item) {
        if (is_file(MAIL_PATH . DS .$item)) {
            $files[] = MAIL_PATH . DS .$item;
        }
    }
    uasort($files, function($a, $b) {
        $time1 = filemtime($a);
        $time2 = filemtime($b);
        if ($time1 == $time2) {
            return 0;
        }
        return ($time1 < $time2) ? -1 : 1;
    });

    libxml_use_internal_errors(false);
    $mails = [];
    $offset = count($files) - MAX_FILES;
    foreach ($files as $i => $file) {
        if ($offset > 0) {
            unlink($file);
            --$offset;
        } else {
            $xml = simplexml_load_file($file);
            $subj = $xml->xpath('.//*[@id = \'subject\']');
            $to = $xml->xpath('.//*[@id = \'to\']');
            $date = $xml->xpath('.//*[@id = \'date\']');
            $mails[] = [
                'link' => '/' . MAIL_DIR . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . pathinfo($file, PATHINFO_EXTENSION),
                'subject' => (string)reset($subj),
                'to' => htmlspecialchars((string)reset($to)),
                'date' => (string)reset($date),
            ];
        }
    }

    uasort($mails, function($a, $b) {
        if ($a['date'] == $b['date']) {
            return 0;
        }
        return ($a['date'] > $b['date']) ? -1 : 1;
    });

    render(
        WWW_PATH . DS . 'index.html',
        BASE_PATH . DS . 'tmpls' . DS . 'list.php',
        [
            'mails' => $mails,
        ]
    );

    $queue->cancel();

    if (php_sapi_name() != 'cli') {
        header('Location: /index.html');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
$conn->disconnect();
