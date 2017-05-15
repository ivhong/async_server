<?php
namespace CommandsController;

use models\SendrecordChuanglan;
use models\MassSms;
use library\Common;
class ChuanglanController{
    public function __construct() {
        $this->redis = Common::getRedis();
    }
    /**
     * 队列出队，接受短信状态的队列监听器
     */
    public function actionListenSmsStatus(){
        while (1) {
            try {
                $value = $this->redis->queueOut(Common::getRedisConfig('keys', 'listen_sms_status'));
                
                if( empty($value) ) continue;
                
                Common::log("-----------------\n短信接受状态队列".json_encode($value), 'Listen', 'Listen', 'smslisten');
                
                $request = [];
                parse_str($value, $request);
                $res = $this->parseSmsStatus($request);
                Common::log("短信接受状态队列本条执行结束：".json_encode([$res]), 'Listen', 'Listen', 'smslisten');
            } catch (\Exception $e) {
                Common::log($e->getMessage(), 'smslisten-error', 'Listen', 'smslisten');
            }
        }
    }
    
    private function parseSmsStatus($request){
        if( !isset($request['msgid']) ){
            Common::log('msgid 为空'.  json_encode($request), 'smslisten-error', 'parseSmsStatus', 'smslisten');
            return false;
        }
        
        $msgid = addslashes($request['msgid']);
        $db = Common::getDB();
        
        $raw = $db->query("SELECT * FROM ".SendrecordChuanglan::tableName()." WHERE msgid=".$msgid);
        Common::log("短信记录查找到的结果".json_encode($raw), 'parseSmsStatus', 'parseSmsStatus', 'smslisten');
        if( !empty($raw) ){
            $raw = $raw[0];
            $sms = $db->query("SELECT * FROM ".MassSms::tableName()." WHERE id=".$raw['m_id']);

            //要更新的notify 任务的值
            $notifyV = [];
            switch ($request['status']){
                case 'DELIVRD':
                    $notifyV[] = '`successed`=`successed` + 1';
                    $field = 'successed';
                    break;
                default:
                    $field = 'failure';
            }
            //更新短信记录表
            $sql = "UPDATE ".SendrecordChuanglan::tableName()." SET `{$field}`=`{$field}` + 1 WHERE msgid=".$msgid;
            $db->query($sql);
            Common::log("短信「记录」修改的sql：".$sql, 'parseSmsStatus', 'parseSmsStatus', 'smslisten');


            $successed = array_sum(array_column($sms, 'successed'));
            $failure = array_sum(array_column($sms, 'failure'));
            $num = array_sum(array_column($sms, 'num'));
            $$field++;
            //如果成功数 +　失败数　＝　总数　那么说明这条任务完成
            if($successed + $failure >= $num){
                $notifyV[] = '`donetime`='.time();
            }

            //如果需要更新notify任务的数据，则更新那张表
            if(!empty($notifyV)){
                $sql = "UPDATE ".MassSms::tableName()." SET ".  implode(',', $notifyV)." WHERE id=".$raw['m_id'];
                $db->query($sql);
                Common::log("短信「任务」修改的sql：".$sql, 'parseSmsStatus', 'parseSmsStatus', 'smslisten');
            }
            unset($field);
            unset($notifyV);
            unset($sms);
            unset($successed);
            unset($num);
        }
        
        unset($raw);
        return true;
    }
}