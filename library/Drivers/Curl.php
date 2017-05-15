<?php
namespace library\Drivers;
use library\Common;
use library\Notify;
use library\Curl as LCurl;
use models\Curl as MCurl;

class Curl extends Driver{
    public $notify_id = 0;
    /**
     * 设置要处理的数据
     *@param int $id 数据库主键
     */
    public function setData($id){
        Common::log('CURL驱动器获得数据ID：'. json_encode($id), get_class($this));
        $this->data = MCurl::getOne($id);
        $this->data['params'] = intval($this->data['params']);
        if(empty($this->data)){
            throw new \Exception('没有要处理的数据');
        }
        Common::log('CURL驱动器获得数据：'. json_encode($this->data), get_class($this));
    }
    
    
    /**
     * 处理数据接口
     */
    public function deal(){
        Common::log('CURL驱动器开始处理：', get_class($this));
        if($this->data['method'] == 'get'){
            list($res, $header, $error) = LCurl::get($this->data['url'], 2, true);
        }elseif($this->data['method'] == 'post'){
            list($res, $header, $error) = LCurl::post($this->data['url'], json_decode($this->data['data'], 1), json_decode($this->data['header'], 1), 2, true);
        }
        
        $this->data['result'] = $res;
        $this->data['result_header'] = json_encode($header);
        $this->data['result_error'] = $error;
        $this->data['donetime'] = date('Y-m-d H:i:s');
        //status 等于 1 代表正常，status2代表异常，只有异常的状态可以重新发送
        $status = 1;
        if($header['http_code'] != 200 && $this->data['params'] < Common::getConfig('curl', 'retry_num')){
            $status = 2;
            $this->data['params'] += 1;
        }
        MCurl::saveData($this->data);
        Common::log('CURL驱动器处理完毕：'.var_export($this->data, 1), get_class($this));
        
        return $status;
    }
    
    
    /**
     * 处理数据接口
     */
    public function testDeal(){
        Common::log('CURL驱动器开始测试：', get_class($this));
        if($this->data['method'] == 'get'){
            list($res, $header, $error) = LCurl::get($this->data['url'], 2, true);
        }elseif($this->data['method'] == 'post'){
            list($res, $header, $error) = LCurl::post($this->data['url'], json_decode($this->data['data'], 1), json_decode($this->data['header'], 1), 2, true);
        }
        
        $return = true;
        if($header['http_code'] != 200 && $this->data['params'] < Common::getConfig('curl', 'retry_num')){
            $status = 2;
            $this->data['params'] += 1;
            $return = false;
        }
        MCurl::saveData($this->data);
        Common::log('CURL驱动器测试结果：'.var_export($return, 1), get_class($this));
        return $return;
    }
}



