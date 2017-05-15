<?php

/**
 * 通知内容解封封器
 * 与之对应的是的是 /library/NotifyContent.php(封装器)，
 * 
 * 数据包分为两种类型：
 * 1. 典型包：在发送之前现在数据库备份，然后把备份id当做传输数据
 * 使用方式
 * $notifydata = [
        'suid' => $suid,//数据属主，默认0
        'kid' => $kid,//发送数据
        'type' => $type,//包类型
    ];
    $notifyContent = new NotifyContent();
    $notifyContent->set($notifydata);

    $notify = new Notify();
    $notify->send( $notifyContent, $key_suffix='' );
 * 2. 非典型包：直接发送原数据，不在数据库备份
 * $notify = new Notify();
    $notify->send2( $data, $type, $key_suffix='' );
 */

namespace library;

use models\Notify as mNotify;
use models\QueueStatus ;

class NotifyContent {

    protected $driver = null;
    public $redis = null;
    protected $type = null;
    static $counter_key = 'NotifyContent_Counter_';
    const TYPE_SMS = 'sms';
    
    const KEY_NAME = 'NotifyContentID';

    public function __construct() {
        $this->redis = Common::getRedis();
    }

    /**
     * 设置通知内容
     * @param type $data 内容参数
     * @return \library\Notify\NotifyContent
     */
    public function set($data) {
        $this->data = $data;
    }

    public function toNotifyContent($keyname) {
        $data = [
            'suid' => $this->data['suid'],
            'kid' => $this->data['kid'],
            'type' => $this->data['type'],
            'addtime' => time(),
            'keyname' => $keyname,
        ];

        $id = mNotify::saveData($data);
        $res = [self::KEY_NAME => $id];
        return json_encode($res);
    }

    public function setDriver($type) {
        static $drivers = [];
        if (!isset($drivers[$type])) {
            try {
                $className = __NAMESPACE__ . '\\Drivers\\' . ucfirst(strtolower($type));
                $drivers[$type] = new $className;
            } catch (\Exception $ex) {
                Common::log('设置驱动器失败：' . $type, __CLASS__);
                throw $ex;
            }
        }
        $this->driver = $drivers[$type];
    }

    /**
     * 发送数据
     * @param type $params 发送数据
     * @param type $istest 是否是测试发送
     * @return boolean 如果是测试发送，则返会 true：发送成功 或者 false：发送失败
     */
    public function send($params, $istest=false) {
        $typical = false;
        $package = json_decode($params, 1);
        if($package == false || (!isset($package[self::KEY_NAME]) && !isset($package['type']))){//解析失败 或者没有包类型
            Common::log("错误的包". $params, __CLASS__);
            return;
        }elseif( !isset($package[self::KEY_NAME]) ){ //不是典型的包
            Common::log("收到非典型包". $params, __CLASS__);
            $data = $package['data'];
            $type = $package['type'];
            $keyname = $package['keyname'];
        }else{//典型包
            $typical = true;
            //检测数据库里是否有这条记录，并且判断这条记录是否完成
            $item = mNotify::getOne($package[self::KEY_NAME]);
            if (empty($item) || ($item['status'] == 1 && $item['donetime'] > 0)) {
                Common::log('id：' . $package[self::KEY_NAME] . '的这个包不存在，或者已经完成。' . json_encode($item), __CLASS__);
                return;
            }
            
            $data = $item['kid'];
            $type = $item['type'];
            $keyname = $item['keyname'];
        }

        try {
            $this->setDriver($type);
            Common::log('发给消息驱动器：' . json_encode($data), __CLASS__);
            $this->driver->setData($data);
            $this->driver->keyname = $keyname;
            if($istest){
                return $this->driver->testDeal();
            }
            $status = $this->driver->deal();
            $status = $status == 1 ? 1 : 2;
        } catch (\Exception $ex) {
            if($istest){
                return false;
            }
            //异常退出设置该消息为异常状态
            $status = 2;
            Common::log('消息驱动器发送异常退出：' . $ex->getMessage(), __CLASS__);
        }
        
        //如果是典型包，发送状态为非正常，则判断是否需要重新发送
        if($typical && $status == 2 && $this->driver->resend){
            Common::log('消息驱动器从新发送：' . $package[self::KEY_NAME] . '发送时间'.date('Y-m-d H:i:s', $this->driver->resend_time), __CLASS__);
            Notify::resend($package[self::KEY_NAME], $this->driver->resend_time);
        }
        
        //设置队列发送状态
        QueueStatus::updateKey($keyname, $status);
        
        //如果是经典版，并且正确发送，则设置发送状态
        if($typical && $status == 1){
            $this->done($package[self::KEY_NAME], $status);
        }
        
        unset($package);
        unset($item);
        return $status;
    }
    
    public function testSend($data, $type){
        $this->setDriver($type);
        Common::log('发给消息驱动器：' . json_encode($data), __CLASS__);
        $this->driver->setData($data);
        return $this->driver->testDeal();
    }

    //当driver处理包完场时通知。
    public function done($id,$status=1) {
        $data = [
            'id' => $id,
            'status' => $status,
            'donetime' => time()
        ];
        Common::log('该包发送结束' . json_encode($data), __CLASS__);
        mNotify::saveData($data);
    }
    
    /**
     * 重新发送漏发的任务
     */
    public function complementary(){
        $db = Common::getDB();
        $redis = Common::getRedis();
        
        //得到错误队列
        $list = QueueStatus::getErrorList();
        if( !empty($list) ){
            foreach($list as $item){
                if( QueueStatus::denyComplementary($item['key']) ){
                    Common::log('补充发送:'.$item['key'].'禁止重发', __CLASS__, __CLASS__, 'complementary');
                    continue;
                }
                if( !QueueStatus::check($item['key']) ){
                    Common::log('补充发送:'.$item['key'].'还未过封冻期', __CLASS__, __CLASS__, 'complementary');
                    continue;
                }
                
                $sql = "SELECT * FROM ".mNotify::tablename()." WHERE `donetime`='0' AND `keyname`='".$item['key']."' ORDER BY id DESC";
                $connect = $db->getConnect();
                $result = $connect->query($sql);
                //得到第一个值做测试
                $data = $result->fetch_assoc();
                if($data){
                    $package = [self::KEY_NAME => $data['id']];
                    $senddata = json_encode($package);
                    Common::log('测试发送错误对列第一个' . json_encode($item), __CLASS__, __CLASS__, 'complementary');
                    //如果发送成功，则入队
                    if( $this->send($senddata, 1) ){
                        $redis->queueAdd($item['key'], $senddata);
                        Common::log('补充发送所有：' . json_encode($data), __CLASS__, __CLASS__, 'complementary');
                        //所有值入队
                        while ($data = $result->fetch_assoc()){
                            $package = [self::KEY_NAME => $data['id']];
                            $senddata = json_encode($package);
                            $redis->queueAdd($item['key'], $senddata);
                            Common::log('补充发送（错误对列）（其他）' . json_encode($data), __CLASS__, __CLASS__, 'complementary');
                        }
                    }else{
                        //更新队列锁定状态
                        QueueStatus::updateKey($item['key'], 2);
                        Common::log('测试发送失败' , __CLASS__, __CLASS__, 'complementary');
                    }
                }
            }
        }
        
        //得到所有正在执行的对列
        $config = Common::getRedisConfig('keys');
        $rediskeys = $redis->keys($config['notify_redis_key']['key']."*");
        
        //得到"应该"正常的队列
        $list = QueueStatus::getNormalList();
        if( !empty($list) ){
            foreach($list as $item){
                if( QueueStatus::denyComplementary($item['key']) ){
                    Common::log('补充发送:'.$item['key'].'禁止重发', __CLASS__, __CLASS__, 'complementary');
                    continue;
                }
                
                //如果"应该正常"的对列不在真正的对列里，则重新入队
                if( !in_array($item['key'], $rediskeys) ){
                    $sql = "SELECT * FROM ".mNotify::tablename()." WHERE `donetime`='0' AND `keyname`='".$item['key']."'";
                    $connect = $db->getConnect();
                    $result = $connect->query($sql);
                    while ($data = $result->fetch_assoc()){
                        $package = [self::KEY_NAME => $data['id']];
                        $senddata = json_encode($package);
                        $redis->queueAdd($item['key'], $senddata);
                        Common::log('补充发送（正常对列）' . json_encode($data), __CLASS__, __CLASS__, 'complementary');
                    }
                }
                
            }
        }
    }

}
