<?php
namespace library\SmsSender;

interface SmsSenderInterface {
    //return 1，表示任务发送成功
    public function send($data);
}
