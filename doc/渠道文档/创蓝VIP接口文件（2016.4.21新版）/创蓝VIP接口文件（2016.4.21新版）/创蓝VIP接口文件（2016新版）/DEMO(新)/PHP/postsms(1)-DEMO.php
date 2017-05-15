<?php
$code = rand(100000,999999);
$data ="您好，您的验证码是" . $code ;
$_SESSION['code'] = $code;
$post_data = array();
$post_data['account'] = iconv('GB2312', 'GB2312',"用户名");
$post_data['pswd'] = iconv('GB2312', 'GB2312',"密码");
$post_data['mobile'] ="手机号";
$post_data['msg']=mb_convert_encoding("$data",'UTF-8', '平台编码');
$post_data['needstatus']='true';
$url='http://222.73.117.156/msg/HttpBatchSendSM?'; 
$parse = parse_url($url);
var_dump($parse);
for($i=0;$i<10;$i++)
echo "<br />";
$o="";
foreach ($post_data as $k=>$v)
{
   $o.= "$k=".urlencode($v)."&";aa
}
$post_data=substr($o,0,-1);
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($ch) ;
$pos = strpos($result,',');
echo $result;
//用于截取判断状态码
/*$co=substr($result,15,1);
if($co == '0')
echo $co;
else
echo substr($result,15,3);*/

?>