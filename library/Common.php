<?php
namespace library;
use base\mysql;

class Common{
    public static function log($msg, $section, $category='', $filename='notify'){
        $filename = BASE_PATH . '/runtime/logs/'.RUNEVN.'_'.$filename.'_'.date('Ymd').'.log';
        if( !is_string($msg) ){
            $msg = json_encode($msg);
        }
        
        $msg = '['.date('Y-m-d H:i:s') .'] : ['.$section.'] : ' . ($category ? '['.$category.']' : '').$msg."\n";
        error_log($msg, 3, $filename);
    }
    
    public static function checkMobile($mobile){
        $res = is_numeric($mobile);
        return $res;
    }
    
    public static function getDB($name='master'){
        static $db = [], $dbconfig = [];
        if( empty($dbconfig) ){
            $dbconfig = self::loadConfig('db');
        }
        
        if( !isset($db[$name]) ){
            $db[$name] = mysql::instance($dbconfig[$name]);
        }
        
        return $db[$name];
    }
    
    public static function loadConfig($name){
        static $configs = [];
        if( !isset($configs[$name]) ){
            $configs[$name] = include( BASE_PATH.'/config/'.$name.'.php');
        }
        
        return $configs[$name];
    }
    
    public static function getConfig($file, $name){
        $config = self::loadConfig($file);
        
        return $config[$name];
    }
    
    public static function getRedisConfig($handel, $key=''){
        static $config = [];
        if( empty($config) ){
            $config = self::loadConfig('redis');
        }
        
        if($key){
            return $config[$handel][$key];
        }
        
        return $config[$handel];
    }
    
    public static function getRedis($new = false){
        static $redis = null;
        if( !$redis || $new){
            $redis = new Redis(self::getRedisConfig('server'));
        }
        
        return $redis;
    }
    
    public static function apiresult($code, $data=[], $msg = ''){
        static $config = [];
        if( empty($config) ){
            $config = self::loadConfig('api_code');
        }
        $res = ['code'=>$code, 'msg'=> sprintf($config[$code], $msg), 'data'=>$data];
        die( json_encode($res) );
    }
    
    /**
     * 发送经典包，默认（$key_suffix 为空时）每个类型分配一个队列名字,可分别控制每个类型队列的资源分配
     * 通过 config/redis.php 'keys'=>'types' 配置每个类型的资源分配，如果没有，按通用设置
     * 
     * @param type $kid
     * @param type $type
     * @param type $suid
     * @param string $key_suffix
     */
    public static function sendNotify($kid, $type, $suid=0, $key_suffix=''){
        $notifydata = [
            'suid' => $suid,
            'kid' => $kid,
            'type' => $type,
        ];
        $notifyContent = new NotifyContent();
        $notifyContent->set($notifydata);
        $key_suffix = $type. ($key_suffix ? ('_'.$key_suffix) : '');
        
        $notify = new Notify();
        $notify->send( $notifyContent, $key_suffix );
    }
    
    /**
     *生成守护进程
     * 使用方式
     * Common::createDeamon();
     */
    public static function createDeamon() {
        set_time_limit(0);

        // 只允许在cli下面运行  
        if (php_sapi_name() != "cli") {
            die("only run in command line mode\n");
        }

        umask(0); //把文件掩码清0  

        if (pcntl_fork() != 0) { //是父进程，父进程退出  
            exit();
        } 

        if (pcntl_fork() != 0) { //第二次fock子进程  
            exit();
        }


        posix_setsid(); //设置新会话组长，脱离终端  

        chdir("/"); //改变工作目录  

        $user = posix_getpwnam(self::getConfig('deamon', 'user'));
        if ($user) {
            $uid = $user['uid'];
            $gid = $user['gid'];
            $result = posix_setuid($uid);
            posix_setgid($gid);
        } else {
            die('守护进程用户权限设置失败，请重新设置！');
        }

        //关闭打开的文件描述符  
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        
        global $STDIN, $STDOUT, $STDERR;
        $outputfile = self::getConfig('deamon', 'outputfile');
        $dirname = dirname($outputfile);
        if( !file_exists($dirname) ){
            mkdir($dirname);
        }
        
        $STDIN = fopen('/dev/null', "a");
        $STDOUT = fopen($outputfile, "a");
        $STDERR = fopen($outputfile, "a");
    }
    
    public static function alert($title, $desc){
        self::alertWx($desc);
        
//        $config = self::getRedisConfig('keys', 'alert');
//        
//        $params = [
//            'to' => $config['email'],
//            'subject' => $title,
//            'content' => $desc,
//            'fromname' => '队列异步服务'
//        ];
//        
//        self::sendEmail($params);
    }
    
    public static function alertWx($desc){
        $config = self::getConfig('WX', 'alert');
        WX::$access_token = WX::getAccessToken($config['appid'], $config['secret']);
        
        $data = [];
        $data['content'] = [
            "value"=>$desc,
            "color"=>"#173177"
        ];
        
        foreach($this->openids as $openid){
            WX::sendTmplMsg($openid, $config['tmplid'], '', $data);
        }
    }
    
    public static function sendEmail($params){
        if( !isset($params['to']) ){
            throw new \Exception("Not set \$params['to']");
        }
        
        if( !isset($params['subject']) ){
            throw new \Exception("Not set \$params['subject']");
        }
        
        if( !isset($params['content']) ){
            throw new \Exception("Not set \$params['content']");
        }
        
        $params['fromname'] = isset($params['fromname']) ? $params['fromname'] : NULL;
        
        $params['to'] = is_string($params['to']) ? [$params['to']] : $params['to'];
        $logid = uniqid();
        $mailer = new AliyunMailer;
        if( !$mailer->send($params['to'], $params['subject'], $params['content'], $params['fromname']) ){
            self::log($mailer->error['code']. '：' .$mailer->error['msg'], 'sendemail', 'sendemail', 'sendemail');
        }
    }
}