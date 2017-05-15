# 说明：
# 以下代码只是为了方便客户测试而提供的示例代码，客户可以根据自己的需要另行编写
# 该代码仅供学习和研究接口使用，只是提供了一个参考

require 'typhoeus'

# 发送短信接口URL，如无必要，该参数可不用修改
api_send_url="http://222.73.117.156/msg/HttpBatchSendSM"
# 创蓝帐号，替换成您自己的帐号
account="xxxxxx"
# 创蓝密码，替换成您自己的密码
pswd="xxxxxx"

body={account:account,pswd:pswd,mobile:"13800138000",msg:"您好，您的验证码是1234",needstatus:"true"}

resp=Typhoeus::Request.post(api_send_url,body:body)
puts resp.body
