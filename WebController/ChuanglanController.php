<?php
/**
 * 报警
 */
namespace WebController;

use library\Common;
use library\Request;
class AlertController
{
    public function __construct() {
        $this->redis = Common::getRedis();
    }

    //接受短信平台的发送状态
    public function actionStatusp(){
        $this->redis->RPUSH(Common::getRedisConfig('keys', 'listen_sms_status'), $_SERVER['QUERY_STRING']);
        die('ok');
    }
    
    //接收短信平台的上行数据（接收短信）
    public function actionListen(){
        $request = Request::get();
        Common::log('短信平台上行数据'.  json_encode($request), 'sms_receive');
        $_ = str_split($request['moTime'], 2);
        $totime = '20'. $_[0]. '-' .$_[1]. '-' .$_[2]. ' ' .
                    $_[3]. ':'.$_[4]. ':00';
        ReceiveChuanglan::saveData(['mobile'=>$request['mobile'],'content'=>trim($request['msg']),'totime'=>strtotime($totime),'addtime'=>time(), 'destcode'=>$request['destcode'], 'spcode'=>$request['spCode'], 'request'=>json_encode($request)]);
    }
}