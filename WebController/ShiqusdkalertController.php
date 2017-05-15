<?php
/**
 * 时趣sdk报警
 */
namespace WebController;

use library\Common;
use library\Request;
use models\ShiquSdkAlert;
class ShiqusdkalertController 
{
    public function __construct() {
        $this->redis = Common::getRedis();
    }

    //接受短信平台的发送状态
    public function actionIndex(){
        $data = [];
        $data['desc'] = Request::request('desc');
        $data['apiurl'] = Request::request('apiurl');
        $data['method'] = Request::request('method');
        $data['params'] = Request::request('params');
        $data['curl_info'] = Request::request('curl_info');
        $data['curl_err'] = Request::request('curl_err');
        $data['status'] = 0;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $id = ShiquSdkAlert::saveData($data);
        
        Common::sendNotify($id, 'shiqusdkalert');
        
        Common::apiresult(200, ['id'=>$id]);
    }
    
    public function log($txt){
        if(is_array($txt) || is_object($txt)){
            $txt = json_encode($txt, 1);
        }
        
        error_log($txt."\n", 3, '/home/sqwangchanghong/www/async_server/runtime/logs/t');
    }
    
    public function actionShow(){
        $id = Request::get('id');
        echo '<pre>';
        var_export(ShiquSdkAlert::getOne($id));
        echo '</pre>';
    }

}