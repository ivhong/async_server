<?php
namespace base;
use library\NotifyContent;
use library\Notify;
use library\Common;
use library\Request;
use models\MassSms;
abstract class ApiController{
    //每条短信的长度
    protected $num_per_sms = 70;
    public $content = '';
    public $recordname = '';
    public $receivers = '';
    public $totalNum = '';
    public $user = '';
    
    public function __construct($content, $recordname, $receivers, $auth) {
        //判断参数
        if(!$content || !$receivers){
            Common::apiresult(103);
        }
        
        //判断电话号码的可用性
        $receivers = explode(',', $receivers);
        array_map(function($a){
            if( !Common::checkMobile($a) ){
                Common::apiresult(104);
            }
        }, $receivers);
        
        
        //设置短信属性
        $this->content = $content;
        $this->recordname = $recordname;
        $this->receivers = $receivers;
        
        //设置发送内容
        $this->setSendContent();
        //设置总条数
        $this->setTotalNum();
        
        //设置Reids句柄
        $this->redis = Common::getRedis();
        //设置权限
        $this->setAuth($auth);
    }
    
    public function setAuth($auth){
        $this->auth = $auth;
        $this->user = $auth->user;
    }
    
    /**
     * 获取渠道
     */
    public function getChannel(){
        return $this->user['channel'];
    }


    /**
     * 检查发送的短信数量是否超过剩余数量
     * @param type $num
     * @return boolean
     */
    public function checkNum(){
        if($this->user['total'] < $this->user['used'] + $this->totalNum ){
            return false;
        }
        return true;
    }
    
    /**
     * 发送一个通知任务
     * @param type $kid 外键id，该任务所对应具体任务内容的id
     * @param type $num 该任务包含多少数据
     */
    protected function sendToNotify($kid, $type){
        Common::sendNotify($kid, $type, $this->user['id'], $this->getChannel());
    }
    
    /**
     * 发送
     */
    public function send(){
        //检查剩余流量是否足够
        if( !$this->checkNum() ){
            Common::apiresult(107);
        }
        
        //短信数据
        $data = [
            'suid' => $this->user['id'],
            'content' => $this->content,
            'recordname' => $this->recordname,
            'receivers' => json_encode($this->receivers),
            'num' => $this->totalNum,
            'successed' => 0,
            'donetime' => 0,
            'channel' => $this->getChannel(),
            'addtime' => time(),
        ];
        
        $id = MassSms::saveData($data);
        
        //发送Notify数据
        $this->sendToNotify($id, NotifyContent::TYPE_SMS);
        //扣除使用流量
        $this->auth->consume($this->totalNum);
        
        //返回200
        Common::apiresult(200);
    }
    
    /**
     * 设置短信发送内容
     * @param type $content 短信内容
     * @param type $recordname 备案名
     */
    public function setSendContent(){
        $this->sendContent = ($this->recordname ? '【'.$this->recordname.'】' :  '' ).$this->content;
    }
    
    /**
     * 设置与发送短信总的数量
     */
    public function setTotalNum(){
        $this->totalNum = count($this->receivers) * ceil(mb_strlen($this->sendContent, 'utf-8') / $this->num_per_sms);
    }
    
}