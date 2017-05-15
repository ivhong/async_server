<?php
namespace library\SmsSender;

use library\Common;
use library\Curl;
use models\SendrecordChuanglan;
use models\Users;

class Chuanglan implements SmsSenderInterface {
    public static function getConfig(){
        static $config = [];
        if( empty($config) ){
            $config = Common::loadConfig('chuanglan');
        }
        
        return $config;
    }
    
    public static function getUsers(){
        static $users = [];
        if(empty($user) && $_ = \models\Users::getList("`channel`='chuanglan'")){
            foreach($_ as $user){
                $auth = json_decode($user['params']);
                $users[$user['id']]['user'] = $auth[0];
                $users[$user['id']]['pwd'] = $auth[1];
            }
        }
        return $users;
    }
    
    /**
     * 发送短信
     * @param type $data 数据
     */
    public function send($data) {
        $config = self::getConfig();
        $config['users'] = self::getUsers();
        if( !isset($config['users'][$data['suid']]) ){
            throw new \Exception('在创蓝配置文件中 SUID 没有对应的创蓝账户：'.$data['suid']);
        }
        
        $mobiles = json_decode($data['receivers']);
        
        $res = [];
        $i = 0;
        while($to = array_splice($mobiles, 0, $config['sms_oncesendtotal'])){
            $i++;
            $content = ($data['recordname'] ? '【'.$data['recordname'].'】' : '').$data['content'];
            Common::log('第'.$i.'批发送短信('.count($to).'个)：'.json_encode([$to, $content, $config['users'][$data['suid']]]), __CLASS__);
            $result = $this->sendSMS($config['users'][$data['suid']]['user'], $config['users'][$data['suid']]['pwd'], implode(',', $to), $content, 'true');
            
            $result = $this->execResult($result);
            $res[] = $result;
            
            if( $result[1] != 0){
                Common::log('第'.$i.'批发送失败：'.json_encode([$result]), __CLASS__);
                continue;
            }
            
            Common::log('第'.$i.'批发送短信结果：'.json_encode([$result]), __CLASS__);
            $item = [
                'm_id'=> $data['id'],
                'msgid' => $result[2],
                'num'=> count($to) * ceil(mb_strlen($content) / $config['num_per_sms']),
                'successed'=> 0,
                'failure'=> 0,
                'addtime'=> time(),
                'lasttime' => time()
            ];
            SendrecordChuanglan::saveData($item);
        }
        unset($to);
        unset($mobiles);
        unset($result);
        unset($item);
        
        return 1;
    }
    
    
    function sendSMS($api_account, $api_password, $mobile, $msg, $needstatus = 'true', $extno = ''){
        $config = self::getConfig();
        //创蓝接口参数
        $postArr = array (
                                  'account' => $api_account,
                                  'pswd' => $api_password,
                                  'msg' => $msg,
                                  'mobile' => $mobile,
                                  'needstatus' => $needstatus,
                                  'extno' => $extno
             );

        return Curl::Post( $config['api_send_url'] , $postArr);
    }

    /**
     * 处理返回值
     * 
     */
    public function execResult($result){
            $result=preg_split("/[,\r\n]/",$result);
            return $result;
    }
    
}
