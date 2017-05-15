<?php

namespace base;

class mysql {

    public $_debug;
    private $_connect;
    private $_connTime = 0;
    private $_hosts = [];
    private $_tables = [];
    private $config = [];
    
    static public function instance($config) {
        $instance = new self();
        $instance->config = $config;
        return $instance;
    }

    public function connect() {
        if (time() - $this->_connTime > 60) {
            if (!is_null($this->_connect)) {
                $this->_connect->close();
            }
            $this->_connect = mysqli_init();
            $this->_connect->options(MYSQLI_CLIENT_INTERACTIVE, 86400);
            $this->_connect->real_connect($this->config['host'], $this->config['user'], $this->config['pwd'], $this->config['db']);
            //$this->_connect = new \mysqli('127.0.0.1:3306', MYSQL_USER, MYSQL_PASSWD, 'message_collect');
            $this->_connTime = time();
        }
    }

    public function query($sql) {
        $this->connect();
        $data = [];
        $result = $this->_connect->query($sql);
        if (isset($result->num_rows)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $data[] = $row;
            }
            $result->close();
        }
        return $data;
    }

    public function insertid() {
        return $this->_connect->insert_id;
    }

    public function __distruct() {
        $this->_connect->close();
    }
    
    public function getConnect(){
        $this->connect();
        return $this->_connect;
    }

}
