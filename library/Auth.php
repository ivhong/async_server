<?php
namespace library;

use models\Users;

class Auth{
    public $user = null;
    function __construct($pwd) {
        $this->user = Users::getList('pass=\''.addslashes($pwd).'\'')[0];
    }
    
    function consume($num){
        $data = [
            'id' => $this->user['id'],
            'used' => $this->user['used'] + $num,
            'uptime' => time()
        ];
        
        Users::saveData($data);
    }
}