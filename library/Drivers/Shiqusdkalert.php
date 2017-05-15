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
    const appid = 'wxaade488e19d86b90';
    const secret = '90d276a4f1911d5a3f720b6a92551b11';
    
    //接受模版消息的openid
    public $openids = [
        //王长宏
        'ofAv2vkO58A3W8Spw58Y7K4gQH1E',
        //Center
        'ofAv2vkD0RBN3LlIg-xyVpxJzih4',
        //杨彬彬
        'ofAv2vh_1SWLAKSX_YjbI6pC57NM',
        //微笑
        'ofAv2vmPo0BKJFSLJH2FMsGU_9yM',
        //Sean
        'ofAv2vrC3_EWravoEpnS65xJHcqk',
        //文伟
        'ofAv2vkDLVpSoz2sIhQWTvYp5Nrw',
        //李伟
        'ofAv2vj8zsk7BEd128XtsAMl9Cpw',
        //蓝枫清
        'ofAv2vkE9iNTn_Vna9-_OnxGOz6Q',
        //安伟
        'ofAv2vql8qLyyS2Ec7mqN-IkD12c',
        //S磊
        'ofAv2vtFtxD6HK-cO0otDasnxz0I', 
        //阿运
        'ofAv2viUn4sAmyrqV4TOPcf5KWzc',
        //王上游
        'ofAv2vmXxDTOtBgW7IK15GtaJF9E',
        
    ];
    //微信模版id
    const tmplid = '09XxWpRT-0z_yRmR6hI6s8hIxaX9HA8mQTP8ScqnN5U';
    
    //详细页面地址
    const url = 'http://as.project.social-touch.com/ShiqusdkAlert/show';
    
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



