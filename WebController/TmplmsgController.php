<?php
/**
 * 公共模版消息
 */
namespace WebController;

use library\Common;
use library\Request;
use models\TmplMsg;
class TmplmsgController 
{
    public function __construct() {
        $this->redis = Common::getRedis();
    }

    //接受短信平台的发送状态
    public function actionIndex(){
        $data = [];
        $data['title'] = Request::request('title');
        $data['desc'] = Request::request('desc');
        $data['openids'] = Request::request('openids');
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $id = TmplMsg::saveData($data);
        
        Common::sendNotify($id, 'tmplmsg');
        
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
        $data = TmplMsg::getOne($id);
        echo '<html>
                    <head>
                        <title>模版消息</title>
                        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0"/>
                        <meta http-equiv="content-type" content="txt/html; charset=utf-8" />
                    </head>
                    <body>
                        <pre>
Title: ' . $data['title'].'
Desc: ' . strip_tags($data['desc']) .'
CreateTime: ' . $data['add_time'].'
                        </pre>
                    </body>
            </html>';
    }

}