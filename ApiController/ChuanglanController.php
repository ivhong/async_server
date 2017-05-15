<?php
namespace ApiController;
use base\ApiController;

class ChuanglanController extends ApiController
{
    public function __construct($content, $recordname, $receivers, $auth) {
        parent::__construct($content, $recordname, $receivers, $auth);
        //蓝渠道发送短信备案名是必须的，否则发送失败，所以这里设置一个默认的备案名
        $this->recordname = $this->recordname ? $this->recordname : '时趣SMS-Server';
    }
}