# async_server
php多进程 + redis队列 实现异步处理服务框架。基于http协议插入或获取结果的简单安全队列。

1. 支持按需启用消费进程，并且根据队列内消息数量增加或减少消费进程数
2. 支持对真实消费者的容错判断以及重试并且恢复队列任务
3. 支持针对对特殊channel的最大资源分配
4. 支持安全队列消息，保证消息在非正常消费前的丢失
5. 支持自我监控及重启
6. 支持警告提醒

>背景介绍和详细文档请参考 doc/异步处理系统.docx(框架介绍)  doc/短信系统.docx（服务定制）
# 部署方式

1. 修改配置文件 <br>

>主要配置文件(系统级别)：<br>
>>a. [path]/config/deamon.php: 守护进程执行权限<br>
>>b. [path]/config/db.php: 数据库配置<br>
>>c. [path]/config/redis.php redis 配置，队列配置，可以真对单独的任务队列做特殊配置<br>
>>d. [path]/config/WX.php 队列异常报警配置，使用的是微信模版消息报警<br>

>其他配置(业务级别)：<br>
>>e. [path]/config/chuanglan.php 创蓝（一个短信渠道）短信渠道配置,如果使用短信渠道，可以参考这个做二次开发<br>
>>f. [path]/config/curl.php curl 运行配置<br>

2. 导入sql doc/async_server.sql

3. 确保[path]/runtime 目录webserver有可写权限

4. 修改 [path]/bin/mqmonitor.sh basepath 和 basecmd 参数

5. 服务器控制
>启动: /bin/mqmonitor.sh start<br>
>停止: /bin/mqmonitor.sh stop<br>
>查看状态: /bin/mqmonitor.sh status<br>

# 日志查看
所有的日志文件均放在 [paht]/runtime／ 文件夹下，文件说明<br>
>cache/access_token [系统级别&业务级别] 微信access token的管理<br>
>deamon/log [系统级别] 守护进程输出<br>
>mq/log* [系统级别]队列主进程日志<br>
>mq/childlog* [系统级别]队列子进程日志<br>
>mq/status [系统级别]队列状态<br>
>mq/pid [系统级别]主进程进程号<br>
>logs/cli_complementary_* [系统级别]错误队列处理日志<br>
>logs/cli_notify_* [系统级别]队列数据日志<br>
>logs/cli_[业务名]* [业务级别]命令行日志<br>
>logs/web_[业务名]* [系统级别&业务级别]web调用日志<br>
>curl.log [业务级别]curl日志<br>


