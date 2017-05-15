#!/usr/local/bin/python
#-*- coding:utf-8 -*-
# Author: jacky
# Time: 14-2-22 下午11:48
# Desc: 短信http接口的python代码调用示例
import httplib
import urllib

#服务地址
host = "222.73.117.156"

#端口号
port = 80

#版本号
version = "v1.1"

#查账户信息的URI
balance_get_uri = "/msg/QueryBalance"

#智能匹配模版短信接口的URI
sms_send_uri = "/msg/HttpBatchSendSM"

#创蓝账号
account  = "xxxx"

#创蓝密码
password = "xxxx"

def get_user_balance():
    """
    取账户余额
    """
    conn = httplib.HTTPConnection(host, port=port)
    conn.request('GET', balance_get_uri + "?account=" + account + "&pswd=" + password)
    response = conn.getresponse()
    response_str = response.read()
    conn.close()
    return response_str

def send_sms(text, mobile):
    """
    能用接口发短信
    """
    params = urllib.urlencode({'account': account, 'pswd' : password, 'msg': text, 'mobile':mobile, 'needstatus' : 'false', 'extno' : '' })
    headers = {"Content-type": "application/x-www-form-urlencoded", "Accept": "text/plain"}
    conn = httplib.HTTPConnection(host, port=port, timeout=30)
    conn.request("POST", sms_send_uri, params, headers)
    response = conn.getresponse()
    response_str = response.read()
    conn.close()
    return response_str 

if __name__ == '__main__':

    mobile = "188xxxxxxxx"
    text = "【创蓝文化】您的验证码是1234"

    #查账户余额
    print(get_user_balance())

    #调用智能匹配模版接口发短信
    print(send_sms(text, mobile))