<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Post.aspx.cs" Inherits="Post" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head runat="server">
    <title>创蓝DEMO</title>
</head>
<body>
    <form id="form1" runat="server">
    <div>
        <table bordercolor="#dcdcdc" cellpadding="4" cellspacing="0" frame="box" rules="none"
            style="border-collapse: collapse">
            <tr>
                <td background="#dcdcdc" class="frmHeader" style="border-right: white 2px solid">参数</td>
                <td background="#dcdcdc" class="frmHeader">值</td>
            </tr>
            <tr>
                <td class="frmText" style="font-weight: normal; color: #000000">account:</td>
                <td><asp:TextBox CssClass="frmInput" Id="Txtaccount" runat="server"  Width="272px" /></td>
            </tr>
            <tr>
                <td class="frmText" style="font-weight: normal; color: #000000">password:</td>
                <td><asp:TextBox  CssClass="frmInput" id="Txtpassword" runat="server"  Width="272px" /></td>
            </tr>
            <tr>
                <td class="frmText" style="font-weight: normal; color: #000000">mobile:</td>
                <td>
                    <asp:TextBox  CssClass="frmInput" id="Txtmobile" runat="server" name="scorpid" Width="272px" /></td>
            </tr>
            <tr>
                <td class="frmText" style="font-weight: normal; color: #000000">content:</td>
                <td>
                    <asp:TextBox  CssClass="frmInput" id="Txtcontent" runat="server" name="sprdid" Width="272px" /></td>
            </tr>
            <tr>    
                <td></td>            
                <td align="center"><asp:Button ID="ButSubmit" runat="server" Text="提交" OnClick="ButSubmit_Click" /></td>
            </tr>
            <tr>    
                <td></td>            
                <td align="center"><asp:Label ID="LabelRetMsg" runat="server"></asp:Label></td>
            </tr>
        </table>
    
    </div>
    </form>
</body>
</html>
