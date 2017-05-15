<?php
namespace library;

use Yii;

class Redis{
    private $redis = null;
    public function __construct($config) {
        $this->params = $config;
        $this->setRedis();
    }
    
    private function setRedis($force=false){
        if(!$this->redis || $force){
            $this->redis = new \Redis();
            $this->redis->connect($this->params['host'], $this->params['port']); 
            if( isset($this->params['pass']) && $this->params['pass'] ){
                $this->redis->auth($this->params['pass']);
            }
        }
    }
    
    /**
     * 正常入队
     * @param type $key
     * @param type $value
     */
    public function queueAdd($key, $value){
        if( strlen($key) > 150 ){
            throw new \Exception('你的key太长了, 最长150字符');
        }
        return $this->RPUSH($key, $value);
    }
    
    /**
     * 反向入队
     * @param type $key
     * @param type $value
     */
    public function queueRAdd($key, $value){
        return $this->LPUSH($key, $value);
    }
    
    /**
     * 出队
     * @param type $key
     * @param type $time
     * @return type
     */
    public function queueOut($key, $time=20){
        $value = $this->BLPOP($key, $time);
        if( $value === null ){
            Common::log(json_encode([$key, $value]), 'NotifyListen', 'NULLLLLLL');
        }
        
        if( empty($value) ) return null;
        
        return $value[1];
    }
    
    public function queueDel($key){
        $value = $this->DEL($key);
    }
    
    public function __call($name, $arguments) {
        if( !method_exists($this, $name) ){
            try {
                $res = call_user_func_array(array($this->redis, $name), $arguments);
                if($res === false){
                    $this->setRedis(TRUE);
                    return call_user_func_array(array($this->redis, $name), $arguments);
                }
                
                return $res;
            } catch (\Exception $ex) {
                $this->setRedis(TRUE);
                return call_user_func_array(array($this->redis, $name), $arguments);
            }
           
        }
        
        return call_user_func_array(array($this, $name), $arguments);
    }
    
    public function __destruct() {
        $this->close();
    }
}
