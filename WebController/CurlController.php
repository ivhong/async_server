<?php
/**
 * curl异步处理系统：
    使用方式：
    <host>/curl/listen?url=[url地址]&method=[get | post]&data=[json(post方式提交的数据)]&ext_id=[扩展id，每个扩展id将维护单独队列]

    例子：http://dev.pps.social-touch.com:30058/curl/listen?url=http://dev.pps.social-touch.com:30055/post.php?0sdkf=sdfef&method=post&data={%22a%22:%22bb%22,%22c%22:%22dddd%22}&ext_id=222

    返回JSON：code 200为正确， data:id 为查询状态凭证

    获得curl结果:
    http://dev.pps.social-touch.com:30058/curl/status?id=4052400
    返回JSON：code 200为正确，如果donetime等于 0，则说明任务还未被执行。若大于零，result为curl结果，result_header为curl header状态，result_error 为curl 错误时错误原因。
 */
namespace WebController;

use library\Common;
use library\Request;
use models\Curl;
class CurlController
{
    //接受短信平台的发送状态
    public function actionListen(){
        $url = Request::get('url');
        $data = Request::get('data');
        $header = Request::get('header');
        $method = Request::get('method', 'get');
        $ext_id = Request::get('ext_id', 0);
        if($url == ''){
            Common::apiresult(110, [], '参数 url 不能为空。');
        }
        
        if($ext_id == ''){
            Common::apiresult(110, [], '参数 ext_id 不能为空。');
        }
        
        if($data == ''){
            $data = '[]';
        }elseif(!json_decode($data, 1)){
            Common::apiresult(110, [], '参数 data 必须为json格式字符串。');
        }
        
        if($header == ''){
            $header = '[]';
        }elseif(!json_decode($header, 1)){
            Common::apiresult(110, [], '参数 header 必须为json格式字符串。');
        }
        
        if(!in_array($method, ['get', 'post'])){
            Common::apiresult(110, [], '参数 method 必须为 get 或者 post。');
        }
        
        $data = [
            'url' => $url,
            'data' => $data,
            'header' => $header,
            'method' => $method,
            'addtime' => date('Y-m-d H:i:s'),
        ];
        
        $id = Curl::saveData($data);
        
        Common::sendNotify($id, 'curl', 0, $ext_id);
        
        Common::apiresult(200, ['id'=>$id]);
    }
    
    public function actionStatus(){
        $id = intval( Request::get('id') );
        
        $data = Curl::getOne($id);
        if( empty($data) ){
            Common::apiresult(110, [], '该数据不存在');
        }else{
            $result = [
                'donetime' => $data['donetime'],
                'result' => $data['result'],
                'result_header' => $data['result_header'],
                'result_error' => $data['result_error'],
            ];
            
            Common::apiresult(200, $result);
        }
    }
}