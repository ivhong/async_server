<?php
/**
 * 时趣sdk报警
 */
namespace library\Drivers;
use library\Common;
use models\ShiquSdkAlert as mShiquSdkAlert;
use library\WX;

class Shiqusdkalert extends Driver{
    public $notify_id = 0;
    
    //郭磊账户
    const appid = '88e19d86b90';
    const secret = '911d5a3f720b6a92551b11';
    
    //接受模版消息的openid
    public $openids = [
        
    ];
    //微信模版id
    const tmplid = 'T-0z_yRmR6hI6s8hIxaX9HA8mQTP8ScqnN5U';
    
    //详细页面地址
    const url = 'http://';
    
    /**
     * 设置要处理的数据
     *@param int $id 数据库主键
     */
    public function setData($id){
        Common::log('Shiqusdkalert驱动器获得数据ID：'. json_encode($id), get_class($this), '', 'shiqusdkalert');
        $this->data = mShiquSdkAlert::getOne($id);
        
        if(empty($this->data) || $this->data['status'] == 1){
            throw new \Exception('没有要处理的数据');
        }
        
        Common::log('Shiqusdkalert驱动器获得数据：'. json_encode($this->data), get_class($this), '', 'shiqusdkalert');
    }

    /**
     * 处理数据接口
     */
    public function deal(){
        Common::log('Shiqusdkalert驱动器开始处理：', get_class($this), '', 'shiqusdkalert');
        
        $accessToken = WX::getAccessToken(self::appid, self::secret);
        WX::$access_token = $accessToken;
        Common::log($accessToken, get_class($this), '', 'shiqusdkalert');
        
        $data = [];
        foreach($this->data as $field => $val){
            $data[$field] = [
                "value"=>$val,
                "color"=>"#173177"
            ];
        }
        
        foreach($this->openids as $openid){
            $this->sendTmplMsg($openid, $data, self::url . "?id=".$this->data['id']);
        }
        
        $this->data['status'] = 1;
        $res = mShiquSdkAlert::saveData($this->data);
        
        Common::log('Shiqusdkalert驱动器处理完毕：'.var_export($res, 1), get_class($this), '', 'shiqusdkalert');
        
        return 1;
    }
    
    private function sendTmplMsg($openid, $data, $url){
        $res = WX::sendTmplMsg($openid, self::tmplid, $url, $data);
        Common::log($openid. ' : '. var_export($res, 1), get_class($this), '', 'shiqusdkalert');
    }
    
    
    /**
     * 处理数据接口
     */
    public function testDeal(){
        return true;
    }
}



