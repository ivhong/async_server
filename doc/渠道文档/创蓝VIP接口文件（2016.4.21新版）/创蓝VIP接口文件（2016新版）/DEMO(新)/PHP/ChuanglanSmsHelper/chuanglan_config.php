﻿<?php
/* *
 * 配置文件
 * 版本：1.2
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究接口使用，只是提供一个参考。
*/
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

//创蓝发送短信接口URL, 如无必要，该参数可不用修改
$chuanglan_config['api_send_url'] = 'http://222.73.117.156/msg/HttpBatchSendSM';

//创蓝短信余额查询接口URL, 如无必要，该参数可不用修改
$chuanglan_config['api_balance_query_url'] = 'http://222.73.117.156/msg/QueryBalance';

//创蓝账号 替换成你自己的账号
$chuanglan_config['api_account']	= 'xxxx';

//创蓝密码 替换成你自己的密码
$chuanglan_config['api_password']	= 'xxxx';

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
?>