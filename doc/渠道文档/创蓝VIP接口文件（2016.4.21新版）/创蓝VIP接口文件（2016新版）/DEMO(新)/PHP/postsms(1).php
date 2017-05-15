

<?php

//session_start();
 function postSMS($mobiel,$data)
{

	$post_data = array();
	$post_data['account'] = "账号";
	$post_data['pswd'] = "密码";
	$post_data['mobile'] = $mobiel;
	$post_data['msg']=$data;
	$url='http://222.73.117.156/msg/HttpBatchSendSM?';
	$o="";
	foreach ($post_data as $k=>$v)
	{
	   $o.= "$k=".urlencode($v)."&";
	}
	$post_data=substr($o,0,-1);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$result = curl_exec($ch);
	curl_close($ch);

}

	postSMS('15601671836','你的验证码是：12365')
?>