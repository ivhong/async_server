<?php
namespace library;

/**
 * 使用阿里云邮件服务发送邮件
 * Class Email
 * @package library
 *
 */
class AliyunMailer{
    const APIURL = 'http://cs0-in/api/email';
    public $key;
    public $token;
    public $apiUrl = self::APIURL;
    public $error = ['code'=>0,'msg'=>''];

    public function __construct($key='',$token=''){
        $this->key = $key ? $key : 'scrm_cs';
        $this->token = $token ? $token : '004b6ee07fc00377119ccb0c18eb3807';
        if(empty($this->key) || empty($this->token)){
            throw new \Exception('无效的key,token',20001);
        }
    }

    public function send($toEmail,$subject,$content, $alias='慈善小助手'){
        $timestamp = time();
        $nonce = uniqid();
        $params = [
            'key'=>$this->key,
            'signature'=>$this->_generateSign($nonce,$timestamp),
            'nonce'=>$nonce,
            'timestamp'=>$timestamp,
            'email'=>implode(',',(array)$toEmail),
            'subject'=>$subject,
            'content'=>$content,
            'alias'=> $alias,
            //'sender' => 1,
            'sender' => 2,
        ];
        
        $rs =  json_decode(Curl::post($this->apiUrl,$params));
        if($rs){
            if($rs->code==200){
                $this->error = [];
                return true;
            }
            
            $msg = !empty($rs->msg) ? $rs->msg : $rs->message;
            $this->error = ['code'=>$rs->code,'msg'=>$msg];
            return false;
        }else{
            $this->error = ['code'=>20002,'msg'=>'接口超时'];
            return false;
        }
    }

    private function _generateSign ( $nonce, $timestamp ){
        $checkparams = array($this->token,$nonce,$timestamp);//安全码$nonce需要自己更换
        sort($checkparams,SORT_STRING);
        $checkstr = implode($checkparams);
        return md5($checkstr);
    }

}