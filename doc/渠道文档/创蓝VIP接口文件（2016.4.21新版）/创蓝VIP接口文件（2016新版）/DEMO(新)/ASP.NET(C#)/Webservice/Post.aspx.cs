using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.IO;
using System.Net;
using System.Text;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;

public partial class Post : System.Web.UI.Page
{
    public static string PostUrl = ConfigurationManager.AppSettings["WebReference.Service.PostUrl"];
    protected void Page_Load(object sender, EventArgs e)
    {

    }
    protected void ButSubmit_Click(object sender, EventArgs e)
    {
        string account = this.Txtaccount.Text.Trim();
        string password = this.Txtpassword.Text.Trim();
        string mobile = this.Txtmobile.Text.Trim();
        string content = this.Txtcontent.Text.Trim();

        string postStrTpl = "account={0}&pswd={1}&mobile={2}&msg={3}&needstatus=true&extno=";

        UTF8Encoding encoding = new UTF8Encoding();
        byte[] postData = encoding.GetBytes(string.Format(postStrTpl, account, password, mobile, content));

        HttpWebRequest myRequest = (HttpWebRequest)WebRequest.Create(PostUrl);
        myRequest.Method = "POST";
        myRequest.ContentType = "application/x-www-form-urlencoded";
        myRequest.ContentLength = postData.Length;

        Stream newStream = myRequest.GetRequestStream();
        // Send the data.
        newStream.Write(postData, 0, postData.Length);
        newStream.Flush();
        newStream.Close();

        HttpWebResponse myResponse = (HttpWebResponse)myRequest.GetResponse();
        if (myResponse.StatusCode == HttpStatusCode.OK)
        {
            StreamReader reader = new StreamReader(myResponse.GetResponseStream(), Encoding.UTF8);
            LabelRetMsg.Text = reader.ReadToEnd();
            //反序列化upfileMmsMsg.Text
            //实现自己的逻辑
        }
        else
        {
            //访问失败
        }
    }
}
