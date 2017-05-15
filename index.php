<?php
/**
 * 脱离yii的命令行框架
 * 执行方式 php cli.php <controller>/<method> ["param1=a&param2=b"]
 */
define('BASE_PATH', __DIR__);
define('RUNEVN', 'web');

include __DIR__ .'/base/define.php';
include __DIR__ .'/base/autoload.php';
if( strpos($_SERVER['REQUEST_URI'], '?') !== false){
    $path = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
}else{
    $path = $_SERVER['REQUEST_URI'];
}

$path = trim($path, '/');

\base\router::run($path, 'WebController');



