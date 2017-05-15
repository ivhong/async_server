import com.bcloud.msg.http.HttpSender;

public class HttpSenderTest {
	public static void main(String[] args) {
		String url = "http://222.73.117.156/msg/";// 应用地址
		String account = "询问对接人";// 账号
		String pswd = "询问对接人";// 密码
		String mobile = "13800210021,13800138000";// 手机号码，多个号码使用","分割
		String msg = "您好，您的验证码是123456";// 短信内容
		boolean needstatus = true;// 是否需要状态报告，需要true，不需要false
		String extno = null;// 扩展码

		try {
			String returnString = HttpSender.batchSend(url, account, pswd, mobile, msg, needstatus, extno);
			System.out.println(returnString);
			// TODO 处理返回值,参见HTTP协议文档
		} catch (Exception e) {
			// TODO 处理异常
			e.printStackTrace();
		}
	}
}
