<?php
namespace CommandsController;

use library\Notify;
use library\Common;
use models\Notify as mNotify;
class TestController
{
    /**
     * 队列出队，发送消息任务队列监听器
     */
    public function actionIndex(){
        $redis = Common::getRedisConfig('keys', 'resettime');
        echo $redis;
    }
    
    public static function getUsers(){
        static $users = [];
        if(empty($user) && $_ = \models\Users::getList("`channel`='chuanglan'")){
            foreach($_ as $user){
                $auth = json_decode($user['params']);
                $users[$user['id']]['user'] = $auth[0];
                $users[$user['id']]['pwd'] = $auth[1];
            }
        }
        return $users;
    }
    
    public static function actionListenAA($argv){
        $redis = Common::getRedis();
        while(1){
            $val = $redis->queueOut('aa');
            Common::log('AA出队:'.  getmypid() .'：value='.$val, 'test', 'test', 'testaa');
            
            sleep(5);
        }
    }
    
    public static function actionListenBB($argv){
        $redis = Common::getRedis();
        while(1){
            $val = $redis->queueOut('bb');
            Common::log('BB出队:'.  getmypid() .'：value='.$val, 'test', 'test', 'testbb');
            
            sleep(1);
        }
    }
    
    
    public static function actionIn($args){
        $redis = Common::getRedis();
        $key = $args[0];
        $loop = $args[1];
        for($i=0; $i<$loop; $i++){
            $redis->queueAdd($key, uniqid());
        }
    }
    
    public function actionFock(){
        $redis = Common::getRedis();
        $pid = pcntl_fork();
        if($pid){
            $keys = $redis->keys("ss*");
            var_dump($keys);
            $redis->queueRAdd('ssfefef', 33);
            $keys = $redis->keys("ss*");
            var_dump($keys);
            exit;
        }else{
//            $redis = Common::getRedis(1);
//            $i = 0;
//            while($i++ < 10){
//                echo 'c开始'."\n";
//                $a = $redis->queueOut('tt');
//                echo 'c'.  var_export($a, 1)."\n";
//            }
        }
    }
    
    public function actionI(){
        $db = Common::getDB();
        $i = 0;
        while($i++ < 20000000){
            $sql = <<<EDO
INSERT INTO `sms_server`.`curl` (`url`, `data`, `header`, `method`, `result_header`, `addtime`, `donetime`) VALUES ('http://dev.pps.social-touch.com:30055/post.php', '{"aa":"bb"}', '[]', 'post', 'null', '2016-07-27 17:14:54', '2016-07-27 17:14:55');
EDO;

            $db->query($sql);
            $sql = "INSERT INTO `sms_server`.`notify` (`kid`, `type`, `addtime`, `donetime`) VALUES (117926, 'curl', 1469770617, 1469770630);";
            $db->query($sql);
        }
    }
    
    public function actionT(){
        $outputfile = Common::getConfig('deamon', 'outputfile');
        $dirname = dirname($outputfile);
        var_dump($dirname);exit;
        (new \CommandsController\NotifyController())->actionListen('SmsServer_notify_redis_key_shiqusdkalert');
    }
}