<?php
define('MAIL_DIR', 'mail');
define('WWW_PATH', BASE_PATH . DS . 'www');
define('MAIL_PATH', WWW_PATH . DS . MAIL_DIR);
define('AMQP_HOSTNAME', '1.2.3.4');
define('AMQP_LOGIN', 'login');
define('AMQP_PASSWORD', 'password');
define('AMQP_PORT', '5672');
define('AMQP_VHOST', 'vhost');
define('EXCHANGE_NAME', 'postmanq');
define('EXCHANGE_TYPE', AMQP_EX_TYPE_FANOUT);
define('ROUTING_KEY', '');
define('QUEUE_NAME', 'postmanq');
define('MAX_FILES', 10);