<?php

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', realpath(dirname(__FILE__) . DS . '..' . DS));

require_once BASE_PATH . '/config.php';
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

            $filename = md5($subject . $from . $to . round(microtime(true) * 1000) . rand(0, 999999)) . '.html';

            render(
                MAIL_PATH . DS . $filename,
                BASE_PATH . DS . 'tmpls' . DS . 'mail.php',
                [
                    'subject' => $subject,
                    'from' => $from,
                    'to' => $to,
                    'date' => $parser->getHeader('date'),
                    'body' => $parser->getMessageBody('html'),
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

    libxml_use_internal_errors(true);
    $mails = [];
    $offset = count($files) - MAX_FILES;
    foreach ($files as $i => $file) {
        if ($offset > 0) {
            unlink($file);
            --$offset;
        } else {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $mails[] = [
                'link' => '/' . MAIL_DIR . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . pathinfo($file, PATHINFO_EXTENSION),
                'subject' => $doc->getElementById('subject')->nodeValue,
                'to' => $doc->getElementById('to')->nodeValue,
                'date' => $doc->getElementById('date')->nodeValue,
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
