package main

import (
	"fmt"
	"io"
	"io/ioutil"
	"net/http"
)
/**
 * account: 短信平台账号
 * pswd: 短信平台密码
 */
const url = "http://222.73.117.156:80/msg/HttpBatchSendSM"
const account = "xxxx" // 这里填写短信平台账号
const pswd = "xxxx" // 这里填写短信平台密码

const smUrl = url + "?account=" + account + "&pswd=" + pswd + "&mobile=%s&msg=%s"

/**
 * 发送验证码
 */
func SendMsgToMobile(mobile string, content string) bool {
	strUrl := fmt.Sprintf(smUrl,mobile,content)
	return RemoteCall(strUrl) != nil
}

/**
 * HTTP通信
 */
func RemoteCall(strUrl string) []byte {
	r, err := http.NewRequest("GET", strUrl, nil)
	if err != nil {
		fmt.Println("http.NewRequest: ", err.Error())
		return nil
	}

	// r.Proto = "HTTP/1.0"
	// r.ProtoMajor = 1
	// r.ProtoMinor = 0
	fmt.Println(r.Proto)

	resp, err := http.DefaultClient.Do(r)
	if err != nil {
		fmt.Println("http.DefaultClient.Do: ", err.Error())
		return nil
	}

	defer resp.Body.Close()
	if resp.StatusCode != http.StatusOK {
		fmt.Println("resp.StatusCode!=http.StatusOK: ", resp.StatusCode)
		return nil
	}

	data, err := ioutil.ReadAll(resp.Body)
	if err != nil && err != io.EOF {
		fmt.Println("ioutil.ReadAll: ", err.Error())
		return nil
	}

	fmt.Println(string(data))
	return data
}
/**
 * 主程序
 * mobile: 手机号码
 */
func main() {
	const mobile = "xxxx" // 这里填写手机号码
	var result = SendMsgToMobile(mobile, "验证码")
	fmt.Println(result)

}
