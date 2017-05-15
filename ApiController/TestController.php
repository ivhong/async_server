<?php

namespace controller;
use library\Drivers\Email;

class TestController
{
    public function actionIndex(){
        $item = [
            'to'=>['email'=>'wangchanghong@social-touch.com'],
            'subject' => 'Test'.date('Y-m-d'),
            'content' => 'xxxxxxxxxxxxxx',
            'fromname' => 'xFromName',
        ];
        $email = new Email;
        $email->send($item);
    }
}
