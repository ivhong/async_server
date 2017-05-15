<?php
/**
 * 公共模版消息
 */
namespace library\Drivers;
use library\Common;
use models\TmplMsg as mTmplMsg;
use library\WX;

class TmplMsg extends Driver{
    public $notify_id = 0;
    
    //郭磊账户
    const appid = 'wxaade488e19d86b90';
    const secret = '90d276a4f1911d5a3f720b6a92551b11';
    
    //微信模版id
    const tmplid = 'isHw7rAe6jUjOgtNtSN3i1FyC4PGxYBZYHXhKRet06o';
    
    //详细页面地址
    const url = 'http://as.project.social-touch.com/tmplmsg/show';
    //所有的模版消息，都要发送给这些人
    public $def_openids = [
        //王长宏
        'ofAv2vkO58A3W8Spw58Y7K4gQH1E', 
        //王上游
        'ofAv2vmXxDTOtBgW7IK15GtaJF9E',
        //李伟
        'ofAv2vj8zsk7BEd128XtsAMl9Cpw',
        //安伟
        'ofAv2vql8qLyyS2Ec7mqN-IkD12c'];
    
    /**
     * 设置要处理的数据
     *@param int $id 数据库主键
     */
    public function setData($id){
        Common::log('TmplMsg驱动器获得数据ID：'. json_encode($id), get_class($this), '', 'tmplmsg');
        $this->data = mTmplMsg::getOne($id);
        
        if(empty($this->data) || $this->data['status'] == 1){
            throw new \Exception('没有要处理的数据');
        }
        
        Common::log('TmplMsg驱动器获得数据：'. json_encode($this->data), get_class($this), '', 'tmplmsg');
    }

    /**
     * 处理数据接口
     */
    public function deal(){
        Common::log('TmplMsg驱动器开始处理：', get_class($this), '', 'tmplmsg');
        
        $accessToken = WX::getAccessToken(self::appid, self::secret);
        WX::$access_token = $accessToken;
        Common::log($accessToken, get_class($this), '', 'tmplmsg');
        
        $data = [];
        foreach($this->data as $field => $val){
            $data[$field] = [
                "value"=>strip_tags($val),
                "color"=>"#173177"
            ];
        }
        
        $openids = array_merge($this->def_openids, explode(',', $this->data['openids']));
        $openids = array_unique($openids);
        Common::log('openids：'.var_export($openids, 1), get_class($this), '', 'tmplmsg');
        
        foreach($openids as $openid){
            $this->sendTmplMsg($openid, $data, self::url . "?id=".$this->data['id']);
        }
        
        $this->data['status'] = 1;
        $this->data['done'] = date('Y-m-d H:i:s');
        $res = mTmplMsg::saveData($this->data);
        
        Common::log('TmplMsg驱动器处理完毕：'.var_export($res, 1), get_class($this), '', 'tmplmsg');
        
        return 1;
    }
    
    private function sendTmplMsg($openid, $data, $url){
        $res = WX::sendTmplMsg($openid, self::tmplid, $url, $data);
        Common::log($openid. ' : '.  var_export($res, 1), get_class($this), '', 'tmplmsg');
    }
    
    /**
     * 处理数据接口
     */
    public function testDeal(){
        return true;
    }
}



