<?php
namespace models;

use library\Common;
class QueueStatus extends BaseModel{
    
    public static $denyComplementaryKeys = [];
    
    //队列名
    public static $key = '';
    //队列名所对应的值
    public static $keydata = '';
    
    public static $list = [];
    public static $errorList = [];
    public static $normalList = [];
    
    public static function tablename(){
        return 'queue_status';
    }
    
    /**
     * 更新队列名的状态
     * @param type $key 队列名
     * @param type $status 1 正常 2不正常
     */
    public static function updateKey($key, $status){
        $list = QueueStatus::getList("`key`='".addslashes($key)."'");
        if(empty($list)){
            $data = [
                'key' => $key,
                'num' => $status == 1 ? 0 : 1,
                'addtime' => time(),
                'uptime' => time(),
            ];
            $list[0] = $data;
        }else{
            $data = [
                'id' => $list[0]['id'],
                'num' => $status == 1 ? 0 : ( $list[0]['num'] + 1) ,
                'uptime' => time(),
            ];
        }
        
        $num = Common::getRedisConfig('keys', 'maxErrorNum');
        $data['id'] = QueueStatus::saveData($data);
        
        if($data['num'] >= $num){
            Common::alert('队列锁定'.$key, $key.'：因为该队列任务连续超过最大错误次数（'.$num.'），所以该队列被锁定');
        }elseif($list[0]['num'] >= $num && $data['num'] == 0){
            Common::alert('队列解锁'.$key, $key.'该队列已经解除锁定！');
        }
        
    }
    
    /**
     * 更新队列名的状态
     * @param type $key 队列名
     */
    public static function check($key){
        $list = QueueStatus::getList("`key`='".addslashes($key)."'");
        if( empty( $list ) ){
            return true;
        }
        
        return $list[0]['uptime'] < (time() - Common::getRedisConfig('keys', 'resettime')) ? 1 : ($list[0]['num'] < Common::getRedisConfig('keys', 'maxErrorNum'));
    }
    
    public static function denyComplementary($key){
        return in_array($key, Common::getRedisConfig('keys', 'denyComplementaryKeys'));
    }
    
    public static function loadList(){
        if(empty(self::$list)){
            self::$list = QueueStatus::getList();
            foreach(self::$list as $item){
                if($item['num'] >= Common::getRedisConfig('keys', 'maxErrorNum')){
                    self::$errorList[] = $item;
                }else{
                    self::$normalList[] = $item;
                }
            }
        }
    }
    
    public static function getErrorList(){
        self::loadList();
        return self::$errorList;
    }
    
    public static function getNormalList(){
        self::loadList();
        return self::$normalList;
    }
}