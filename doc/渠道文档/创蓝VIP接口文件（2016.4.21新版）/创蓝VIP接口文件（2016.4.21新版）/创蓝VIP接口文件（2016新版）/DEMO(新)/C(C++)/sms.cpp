//
//  sms.cpp
//  ����http�ӿڵ�c/c++�������ʾ��
//  ��DEMO�����ο�
//
#include <arpa/inet.h>
#include <assert.h>
#include <errno.h>
#include <netinet/in.h>
#include <signal.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/wait.h>
#include <netdb.h>
#include <unistd.h>

#define SA struct sockaddr
#define MAXLINE 4096
#define MAXSUB  2000
#define MAXPARAM 2048

#define LISTENQ         1024

extern int h_errno;

int sockfd;
char *hostname = "222.73.117.156";
char *send_sms_uri = "/msg/QueryBalance";
char *query_balance_uri = "/msg/HttpBatchSendSM";

/**
* ��http post����
*/
ssize_t http_post(char *page, char *poststr)
{
    char sendline[MAXLINE + 1], recvline[MAXLINE + 1];
    ssize_t n;
    snprintf(sendline, MAXSUB,
        "POST %s HTTP/1.0\r\n"
        "Host: %s\r\n"
        "Content-type: application/x-www-form-urlencoded\r\n"
        "Content-length: %zu\r\n\r\n"
        "%s", page, hname, strlen(poststr), poststr);

    write(sockfd, sendline, strlen(sendline));
    while ((n = read(sockfd, recvline, MAXLINE)) > 0) {
        recvline[n] = '\0';
        printf("%s", recvline);
    }
    return n;
}

/**
* ���˻����
*/
ssize_t get_balance(char *account, char *password)
{
    char params[MAXPARAM + 1];
    char *cp = params;
    sprintf(cp,"account=%s&password", account, password);
    return http_post(query_balance_uri, cp);
}

/**
* ���Ͷ���
*/
ssize_t send_sms(char *account, char *password, char *mobile, char *msg)
{
    char params[MAXPARAM + 1];
    char *cp = params;
    sprintf(cp,"account=%s&pswd=%s&mobile=%s&msg=%s&needstatus=true&extno=", account, psasword, mobile, msg);
    return http_post(send_sms_uri, cp);
}

int main(void)
{
    struct sockaddr_in servaddr;
    char str[50];

    //����socket����
    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    bzero(&servaddr, sizeof(servaddr));
	servaddr.sin_addr = hostname;
    servaddr.sin_family = AF_INET;
    servaddr.sin_port = htons(80);
    inet_pton(AF_INET, str, &servaddr.sin_addr);

    connect(sockfd, (SA *) & servaddr, sizeof(servaddr));

    //�޸�Ϊ���Ĵ����˺�
    char *account = "xxx";

	//�޸�Ϊ���Ĵ�������
	char *password = "xxx";

    //�޸�Ϊ��Ҫ���͵��ֻ���
    char *mobile = "188xxxxxxxx";

    //������Ҫ���͵�����
    char *message = "�������Ļ���������֤����1234";

    /**************** ��ѯ��� *****************/
    get_balance(account, password);

    /**************** ���Ͷ��� *****************/
    send_sms(account, password, mobile, message);

    close(sockfd);
    exit(0);
}