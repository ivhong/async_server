<?php
namespace CommandsController;

use library\Common;
use library\Notify;
use library\NotifyContent;
class NotifyController
{
    /**
     * 队列出队，发送消息任务队列监听器
     */
    public function actionListen($key){
        $notify = new Notify;
        $notify->listen($key);
    }
    
    public function actionComplementary(){
        Common::createDeamon();
        //同一时间只能有一个进程在运行
        $runfile = BASE_PATH . "/runtime/mq/notifyComplementary";
        if(file_exists($runfile)){
            $content = file_get_contents($runfile);
            //防止死锁
            if($content > time() - 1800){
                exit;
            }
        }
        
        Common::log('补充发送:开始执行', __CLASS__, __CLASS__, 'complementary');
        
        file_put_contents($runfile, time());
        
        $NotifyContent = new NotifyContent();
        $NotifyContent->complementary();
        unlink($runfile);
    }
    
}