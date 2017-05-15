<?php
/**
 * 通知出队（监听）器
 * 与之对应的是 /library/Notify 通知入队器
 */
namespace library;

use models\QueueStatus;
class Notify{
    public function __construct() {
        $this->redis = Common::getRedis();
    }
    
    /**
     * 发送典型包
     * 请看NotifyContent页首注释
     * 
     * 如果按类型分队列控制，请使用 \library\Common::sendNotify() 函数
     */
    public function send($notifyContent, $key_suffix=''){
        if($key_suffix){
            $key_suffix = '_' . $key_suffix;
        }
        
        $rediskey = Common::getRedisConfig('keys', 'notify_redis_key');
        $_rediskey = $rediskey['key'] . $key_suffix;
        
        $content = $notifyContent->toNotifyContent($_rediskey);
        Common::log($content, 'NotifySend');
        
        //如果测试发送不成功，则不入队列
        if( $this->checkKey($_rediskey) ){
            $this->redis->queueAdd($_rediskey , $content);
        }
    }
    
    /**
     * 检测该队列是否为正常队列
     * @param type $key
     * @return type
     */
    public function checkKey($key){
        return QueueStatus::check($key);
    }
    
    //发送非典型包
    public static function send2($data, $type, $key_suffix='', $timer = 0){
        if($key_suffix){
            $key_suffix = '_' . $key_suffix;
        }
        
        if($timer){
            $key_suffix .= MQTIMERKEY . time();
        }
        
        $rediskey = Common::getRedisConfig('keys', 'notify_redis_key');
        $_rediskey = $rediskey['key'] . '_'.$type.'_'.$key_suffix;
        $content = [
            'data' => $data,
            'type' => $type,
            'keyname' => $_rediskey,
        ];
        Common::log("发送自定义任务：".json_encode($content), 'NotifySend');
        
        $this->redis->queueAdd($_rediskey , json_encode($content));
    }
    
    /**
     * 重新发送
     * @param type $id 包id
     * @param type $time
     */
    public static function resend($id, $time){
        $content = [
            NotifyContent::KEY_NAME => $id,
        ];
        
        $rediskey = Common::getRedisConfig('keys', 'notify_redis_key');
        $key_suffix = NOTIFYRESEND . MQTIMERKEY . $time;
        $key = $rediskey['key'] . $key_suffix;
        Common::log("重新发送任务：".json_encode($content)."key：".$key, 'NotifySend');
        Common::getRedis()->queueAdd( $key, json_encode($content) );
    }
    
    public function listen( $key ) {
        $notifyContent = new NotifyContent();
        Common::log("\n 开始监控队列{$key}", 'NotifyListen');
        while (1) {
            try {
                //如果该队列为不正常队列，则删除队列
                if( !$this->checkKey($key) ){
                    $this->redis->queueDel($key);
                    break;
                }
                
                $value = $this->redis->queueOut( $key );
                    
                if($value === REDUCESIGNAL){
                    Common::log(getmypid().'队列退出信号：'. $key, 'NotifyListen');
                    break;
                }
                
                if( empty($value) ) continue;
                
                Common::log("\n".json_encode($value), 'NotifyListen');
                $notifyContent->send($value);
            } catch (\Exception $e) {
                Common::log($e->getMessage(), 'NotifyListen-error');
            }
        }
    }
}