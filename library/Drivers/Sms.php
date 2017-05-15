<?php
namespace library\Drivers;
use library\Common;
use models\MassSms;

class Sms extends Driver{
    
    /**
     * 设置要处理的数据
     *@param int $id 数据库主键
     */
    public function setData($id){
        Common::log('短信驱动器获得数据ID：'. json_encode($id), get_class($this));
        $this->data = MassSms::getOne($id);
        if(empty($this->data)){
            throw new \Exception('没有要处理的数据');
        }
        Common::log('短信驱动器获得数据：'. json_encode($this->data), get_class($this));
        $this->setSender($this->data['channel']);
    }
    
    public function setSender($channel){
        static $senders = [];
        if( !isset($senders[$channel]) ){
            try {
                $class = '\library\SmsSender\\'.  ucfirst($channel);
                $senders[$channel] = new $class;
            } catch (\Exception $ex) {
                throw new \Exception('没有找到短信发送器：'.$class);
            }
        }
        Common::log('短信驱动器设置发送渠道为：'. $channel, get_class($this));
        $this->sender = $senders[$channel];
    }
    
    /**
     * 处理数据接口
     */
    public function deal(){
        $res = $this->sender->send($this->data);
        return $res;
    }
    
    public function testDeal() {
        return true;
    }
}



