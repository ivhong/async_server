<?php
/* 
 * redis 队列管理程序
 * 
 * 1. 优化定时
 * 2. 优化重试队列
 */
namespace CommandsController;

use library\Common;

class MqController{
    
    private $pidfile = '';
    //如果这个文件存在，代表正在停止服务
    private $letstopfile = '';
    private $pid = 0;
    private $statusfile = '';
    public function __construct() {
        $this->pidfile = BASE_PATH . '/runtime/mq/pid';
        $this->letstopfile = BASE_PATH . '/runtime/mq/letstopfile';
        $this->pidfile = BASE_PATH . '/runtime/mq/pid';
        $this->statusfile = BASE_PATH . '/runtime/mq/status';
    }
    
    public function actionStart(){
        if( $this->checkStart() ){
            echo "队列管理程序已经运行！\n";
            exit;
        }
        echo "队列管理程序已经启动！\n";
        
        //变成守护进程
        Common::createDeamon();
        
        $this->setPid();
        
        $redis = Common::getRedis();
        $config = Common::getRedisConfig('keys');
        $listenkeys = $config['notify_redis_key'];
        
        $worker = new Worker();
        $worker->max_auxiliary_child = $listenkeys['max_auxiliary_child'];
        $worker->resend_max_auxiliary_child = $listenkeys['resend_max_auxiliary_child'];
        $worker->log("队列管理程序已经启动！");
        while(1){
            //进程是否收到停止信号
            if($this->checkLetStop()){
                $worker->log("收到停止信号");
                $worker->stopAll();
                $this->stopAfter();
                $worker->log("队列管理程序已经停止信号");
                break;
            }
            
            $jobs = [];
            $rediskeys = $redis->keys($listenkeys['key']."*");
            
            if($rediskeys !== false){
                foreach($rediskeys as $k){
                    //特殊的key，使用特定的配置
                    if(isset($listenkeys['specific'][$k])){
                        $jobs[$k] = $listenkeys['specific'][$k];
                    }else{
                        //如果是错误重发的key，则使用错误重发的配置
                        if(strpos($k, NOTIFYRESEND) !== false){
                            $jobs[$k] = $listenkeys['resend_handle'];
                        }else{
                            $type = $this->getTypeFromKey($listenkeys['key'], $k);
                            //如果有类型，并且类型有特殊的分配资源策略
                            if( $type && isset($listenkeys['types'][$type]) ){
                                $jobs[$k] = $listenkeys['types'][$type];
                            }else{//其他为通用的分配资源策略
                                $jobs[$k] = $listenkeys['handle'];
                            }
                        }
                    }
                }
            }
            
            $worker->setJobs($jobs);
            $worker->run();
            $worker->exportStatus($this->statusfile);
            
            sleep(3);
        }
    }
    
    /**
     * 从队列名里解析出类型来
     * 如下规格：固定前缀_type 或者 固定前缀_type_后缀
     */
    public function getTypeFromKey($prefix, $key){
        $type = '';
        $pos = strlen($prefix) + 1;
        if($str = substr($key, $pos) ){
            if( $_pos = strpos($str, '_') ){
                    $type = substr($str, 0, $_pos);
            }else{
                    $type = $str;
            }
        }
        return $type;
    }
    
    public function actionStop(){
        if( !$this->checkStart() ){
            echo "队列管理程序没有运行！\n";
        }else{
            $this->letStop();
            echo '正在停止服务，请不要强行停止！！！';
            do{
                sleep(1);
                echo '.';
            }  while (file_exists ($this->letstopfile) || file_exists ($this->pidfile));

            echo "\n队列管理程序已经停止！\n";
        }
    }
    
    public function actionRestart(){
        $this->actionStop();
        $this->actionStart();
    }
    
    public function actionReload(){
        $this->actionRestart();
    }
    
    public function actionStatus(){
        echo file_get_contents($this->statusfile);
    }
    
    public function checkStart(){
        return file_exists($this->pidfile);
    }
    
    public function setPid(){
        $pididr = dirname( $this->pidfile );
        if( !file_exists($pididr) ){
            mkdir($pididr, 755);
        }
        
        $this->pid = getmypid();
        file_put_contents($this->pidfile, $this->pid);
    }
    
    public function getPid(){
        return file_get_contents($this->pidfile);
    }
    
    public function stopAfter(){
        $this->delPidFile();
        unlink($this->letstopfile);
    }
    
    public function delPidFile(){
        if( file_exists($this->pidfile) ){
            unlink($this->pidfile);
        }
    }
    
    public function letStop(){
        file_put_contents($this->letstopfile, '1');
    }
    
    public function checkLetStop(){
        return file_exists($this->letstopfile);
    }
}

class Worker{
    public $prejobs = [];
    
    private $runjobs = [];
    private $locks = [];
    private $locksJobjs = [];
    private $lock_max_time = 60;
    private $redis;
    private $alertmobiles = '15910708920';
    private $starttime = 0;
    //辅助进程最大总数
    public $max_auxiliary_child = 10;
    //首次发送失败，重试发送的最大辅助进程数
    public $resend_max_auxiliary_child = 6;
    //辅助进程总数，每个队列都要至少有一个处理进程，其他的进程为辅助进程
    private $total_auxiliary_child = 0;
    private $resend_total_auxiliary_child = 0;
    private $total_pid_num = 0;
    private $resend_total_pid_num = 0;
    
    function __construct() {
        $this->redis = Common::getRedis();
        $this->starttime = time();
    }
    
    //设置所有的工作内容
    function setJobs($jobs){
        $this->prejobs = $jobs;
    }
    
    //执行
    function run(){
        foreach($this->prejobs as $key => $job){
            //如果是定时任务
            if(strpos($key, MQTIMERKEY) !== false){
                $_tmp = explode(MQTIMERKEY, $key);
                //如果还未到时间，则继续下一个循环
                $this->log("获得定时任务{$key}，任务时间".date('Y-m-d H:i:s', $_tmp[1]));
                if($_tmp[1] > time()){
                    continue;
                }
            }
            
            if( !isset($this->runjobs[$key]) ){
                $this->runjobs[$key] = $job;
            }
        }
        $this->preapportion();
    }
    
    //准备分配资源
    function preapportion(){
        $this->total_pid_num = 0;
        $this->resend_total_pid_num = 0;
        foreach($this->runjobs as $key => $job){
            $default = [
                'key' => $key,
                'pids' => []
            ];
            $job += $default;
            
            $this->runjobs[$key] = $this->apportion($job);
           
            if(strpos($job['key'], NOTIFYRESEND) !== false){
                $_k = 'resend_total_pid_num';
            }else{
                $_k = 'total_pid_num';
            }
            
            $this->$_k += count($this->runjobs[$key]['pids']);
            //如果没有处理的进程，则在运行的工作列表里删除
            if( empty($this->runjobs[$key]['pids']) ){
                unset($this->runjobs[$key]);
            }
        }
        
        
        $this->log("共有【正常】进程个数：".$this->total_pid_num);
        $this->log("共有【重试】进程个数：".$this->resend_total_pid_num);
        $this->log("共有【正常】辅助进程个数：".$this->total_auxiliary_child);
        $this->log("共有【重试】辅助进程个数：".$this->resend_total_auxiliary_child);
    }
    
    
    
    function stopAll(){
        $this->log("正在停止所有工作进程！");
        foreach($this->runjobs as $job){
            $pnum = count($job['pids']);
            //减少
            do{
                $this->redis->queueRAdd($job['key'], REDUCESIGNAL);
                $pnum--;
                $this->log("{$job['key']}:发送一个减少进程信号");
            }while ($pnum > 0);
        }
        
        do{
            foreach($this->runjobs as $key => $job){
                $job['pids'] = $this->checkPids($job['pids']);
                if( empty($job['pids']) ){
                    unset($this->runjobs[$key]);
                }
            }
            sleep(1);
        }while(!empty ($this->runjobs));
        $this->log("所有工作进程已经停止！");
    }
    
    
    //分配资源
    function apportion($job){
        if(strpos($job['key'], NOTIFYRESEND) !== false){
            $key = 'resend_total_auxiliary_child';
            $key1 = 'resend_total_pid_num';
        }else{
            $key = 'total_auxiliary_child';
            $key1 = 'total_pid_num';
        }
        //进程每个子进程的状态
        $prevnum = count($job['pids']);
        $job['pids'] = $this->checkPids($job['pids']);
        $nextnum = count($job['pids']);
        //当有进程减少时,并且至少有过一个守护进程
        if($nextnum < $prevnum && $prevnum > 1){
            $nextnum = $nextnum == 0 ? 1 : $nextnum;
            $this->$key -= $prevnum - $nextnum;
        }
        //现在的进程数
        $pnum = count($job['pids']);
        //判断当前job是否有锁
        if( !$this->checkLockJobs($job['key'], $pnum) ){
            $this->log("{$job['key']}:锁等待{$pnum}:{$this->locksJobjs[$job['key']]['value']}");
            return $job;
        }
        
        
        //解锁
        $this->unlockJobs($job['key']);
        
        //队列中的值
        $vnum = $this->redis->llen($job['key']);
        if($vnum === false){
            return $job;
        }
        
        //需要的进程数
        $nnum = ceil($vnum / $job['doScritpNum']);
        $this->log("{$job['key']}:现有进程数{$pnum}， 需要进程数{$nnum}，队列内值数量{$vnum}");
        //如果需要的进程数大于现有的进程数，添加进程
        if($nnum > $pnum){
            //是否可以添加进程
            if($this->checkAddJob($job, $pnum)){
                $pid = $this->addJob($job);
                $job['pids'][] = $pid;
                $pnum++;
                //如果该队列的监控进程大于一个，每次增加一个，则代表增加一个辅助进程
                if($pnum > 1){
                    $this->$key++;
                }
                $this->log("{$job['key']}:成功添加一个进程{$pid}");
            }else{ //如果不能添加则报警
                $this->alert($job, $pnum, $nnum, $vnum);
            }
            
        }elseif($nnum < $pnum){//如果需要的进程数小于需要的进程数
            $reducenum = $pnum - $nnum;
            $reduce = 0;
            //减少进程
            do{
                $res = $this->redis->queueRAdd($job['key'], REDUCESIGNAL);
                if( !$res ){
                    break;
                }
                $reduce++;
                $reducenum--;
                $this->log("{$job['key']}:发送一个减少进程信号".  var_export($res, 1));
            }while ($reducenum > 0);
            $this->log("添加锁".($pnum-$reduce));
            //加减少进程数锁
            $this->setLockJobs($job['key'], 'reduce', $pnum-$reduce);
        }
        
        return $job;
    }
    
    
    function checkAddJob($job, $pnum){
        //如果该队列没有任何监控，则添加
        if( empty($job['pids']) ) return true;
        
        if( strpos($job['key'], NOTIFYRESEND) !== false){
            if( $this->resend_total_auxiliary_child >= $this->resend_max_auxiliary_child ){
                return false;
            }
        }elseif($this->total_auxiliary_child >= $this->max_auxiliary_child){
            return false;
        }
        
        return $job['doScritpNumMax'] > $pnum;
    }
    
    function alert($job, $pnum, $nnum, $vnum){
        if( !$this->checkLock('alert', $job['key'], $job['doAlertTime']) ) return;
        $content = <<<EDO
队列 {$job['key']} 超载: 现有进程数{$pnum}， 需要进程数{$nnum}，队列内值数量{$vnum}
EDO;
        
//        $url="http://dev.pps.social-touch.com:30050/api.php?pwd=abc&content=$content&receivers=$this->alertmobiles";
//        $this->log($job['key'] . "报警");
//        $res = file_get_contents($url);
        Common::alert('', $content);
        
        $this->setLock('alert', $job['key']);
    }
    
    function setLock($type, $name){
        $this->locks[$type][$name] = time();
    }
    
    function checkLock($type, $name, $expire = 0){
        $expire = $expire ? $expire : $this->lock_max_time;
        if( isset($this->locks[$type][$name]) && time() - $this->locks[$type][$name] < $expire){
            return false;
        }
        return true;
    }
    
    function addJob($job){
        $pid = pcntl_fork();
        if($pid){
            return $pid;
        }else{
            $this->childLog("子进程已经启动". getmypid());
            //重置子进程redis连接
            $redis = Common::getRedis(1);
            (new \CommandsController\NotifyController())->actionListen($job['key']);
            $this->childLog("子进程已经退出". getmypid());
            $redis->close();
            exit;
        }
    }
    
    function checkPids($pids){
        foreach($pids as $key => $pid ){
            if(!$this->pidexists($pid)){
                $this->log("子进程{$pid}已经退出！");
                unset($pids[$key]);
            }
        }
        return $pids;
    }
    
    function pidexists($pid){
        //$res == 0 是表示进程还在
        $res = pcntl_waitpid($pid, $status, WNOHANG);
        return $res === 0;
    }
    
    function setLockJobs($key, $type, $value){
        $this->locksJobjs[$key] = [
            'type' => $type,
            'value' => $value,
            'time' => time(),
        ];
    }
    
    function checkLockJobs($key, $value){
        if( isset($this->locksJobjs[$key])//有锁
                &&
           //锁在有效期内
           time() - $this->locksJobjs[$key]['time'] < $this->lock_max_time){
            if(($this->locksJobjs[$key]['type'] == 'add'//锁的类型是加锁
                    &&
               $value >= $this->locksJobjs[$key]['value'])//值比锁的值等于或者大
                    ||
               ($this->locksJobjs[$key]['type'] == 'reduce'//锁的类型是减锁锁
                    &&
               $value <= $this->locksJobjs[$key]['value'])//值比锁的值等于或者小
                    ){
                return true;
             }
             
             return false;
        }
        
        return true;
    }
    
    function unlockJobs($key){
        if( isset($this->locksJobjs[$key]) ){
            unset($this->locksJobjs[$key]);
        }
    }
    
    function log($msg){
        error_log(date('Y-m-d H:i:s') ."：" .$msg."\n", 3 , BASE_PATH . '/runtime/mq/log'.date('Y-m-d'));
    }
    
    function childLog($msg){
        error_log(date('Y-m-d H:i:s') ."：" .$msg."\n", 3 , BASE_PATH . '/runtime/mq/childlog'.date('Y-m-d'));
    }
    
    function exportStatus($statusfile){
        $txt = "状态更新时间：".date('Y-m-d H:i:s').PHP_EOL;
        $txt .= "服务器启动时间：".date("Y-m-d H:i:s", $this->starttime).PHP_EOL;
        $txt .= "服务器已经运行".(time() - $this->starttime)."秒".PHP_EOL;
        if(!empty($this->runjobs)){
            $pnum = 0;
            foreach($this->runjobs as $job){
                $pnum += count($job['pids']);
                $txt .= "{$job['key']} 正在被监控，其子进程有".count($job['pids'])."个".PHP_EOL;
            }
            $txt .= "共有{$pnum}个子进程正在运行！".PHP_EOL;
            $txt .= "其中【正常】辅助进程数{$this->total_auxiliary_child}！".PHP_EOL;
            $txt .= "其中【重试】辅助进程数{$this->resend_total_auxiliary_child}！".PHP_EOL;
        }else{
            $txt .= "现在没有被监控的队列".PHP_EOL;
        }
        $txt .= '使用内存：'.round(memory_get_usage()/1024/1024, 2).'MB'.PHP_EOL;
        file_put_contents($statusfile, $txt);
    }
}