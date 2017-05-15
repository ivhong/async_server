<%@LANGUAGE="VBSCRIPT" CODEPAGE="65001"%>
<%
Function Post(url,data)
	dim Https 
	set Https=server.createobject("MSXML2.XMLHTTP")
	Https.open "POST",url,false
	Https.setRequestHeader "Content-Type","application/x-www-form-urlencoded"
	Https.send data
	if Https.readystate=4 then
		dim objstream 
		set objstream = Server.CreateObject("adodb.stream")
		objstream.Type = 1
		objstream.Mode =3
		objstream.Open
		objstream.Write Https.responseBody
		objstream.Position = 0
		objstream.Type = 2
		objstream.Charset = "utf-8"
		Post = objstream.ReadText
		objstream.Close
		set objstream = nothing
		set https=nothing
	end if
End Function

dim target,post_data
target = "http://222.73.117.156/msg/HttpBatchSendSM"
post_data = "account=创蓝账号&pswd=创蓝密码&mobile=手机号&msg="&Server.URLEncode("短信测试")&"&needstatus=true&extno="

response.Write(Post(target,post_data))
''//请自己解析Post(target,post_data)返回的字符串并实现自己的逻辑
''第一行xxxxxxx,0表示成功,其它的参考http.doc文档
%>