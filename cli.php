<?php
/**
 * 执行方式 php cli.php <controller>/<method> ["param1=a&param2=b"]
 */
if(PHP_SAPI != 'cli'){
    die('嘿嘿！~');
}
define('BASE_PATH', __DIR__);
define('RUNEVN', 'cli');
ini_set('default_socket_timeout', -1);  //不超时

include __DIR__ .'/base/define.php';
include __DIR__ .'/base/autoload.php';


array_shift($argv);

if( empty($argv) ){
    die('正确的调用方式 php cli.php <controller>/<method>');
}

$path = array_shift($argv);

\base\router::run($path, 'CommandsController', $argv);



