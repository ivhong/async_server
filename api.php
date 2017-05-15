<?php
/**
 * api 入口
 */
##########################
#设置环境
##########################
define('BASE_PATH', __DIR__);
define('RUNEVN', 'api');


include __DIR__ .'/base/define.php';
include __DIR__ .'/base/autoload.php';



$uri = $_SERVER['REQUEST_URI'];
if(strpos($_SERVER['REQUEST_URI'], '?') !== false){
    $path = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?') );
}else{
    $path = substr($_SERVER['REQUEST_URI'], 0);
}


use library\Request;
use library\Common;
use library\Auth;
use models\Users;
##########################
#判断权限
##########################
$reuqest = \library\Request::get();
if( empty($reuqest['pwd']) ){
    Common::apiresult(108);
}

$pwd = $reuqest['pwd'];
$auth = new Auth($pwd);
if( empty($auth->user) ){
    Common::apiresult(109);
}

##########################
#设置管理参数以及设置渠道
##########################

$content = empty($reuqest['content']) ? '' : $reuqest['content'];
$receivers = empty($reuqest['receivers']) ? '' : $reuqest['receivers'];
$recordname = empty($reuqest['recordname']) ? '' : $reuqest['recordname'];

$class = "\ApiController\\".ucfirst($auth->user['channel'])."Controller";
try {
    $sender = new $class($content,$recordname, $receivers, $auth);
} catch (\Exception $ex) {
    Common::apiresult(106, [], $class);
}

$sender->send();