#!/bin/sh

########################################################
###MQ 监控脚本，mq运行时监控
###执行方式：sh mqmonitor.sh
###
#作者：王长宏
#时间：2016-08-01
#网址：ivhong.com
#邮箱：hong350@163.com
########################################################
basepath="/home/sqwangchanghong/www/async_server"
basecmd="/home/pubsrv/php-5.5.18/bin/php ""$basepath""/cli.php"

mqcommond="$basecmd"" mq";
statusfile="$basepath""/runtime/mq/status";
pidfile="$basepath""/runtime/mq/pid";
stopfile="$basepath""/runtime/mq/letstopfile";

isRuning()
{
    if [ -f "$pidfile" ]; then
        return 1
    fi
    return 0
}

#错误输出
errorInput()
{
    echo "错误的参数，请按如下方式调用："
    echo "sh mqmonitor.sh [start | stop | status]"
    exit
}

i=0

start()
{
    if [ -f "$pidfile" ]; then
        exit
    fi

    while true;
    do
        isRuning
        res=$?
        if [ "$res" == "1" ]; then
            time=`stat -c %Y $statusfile`
            now=`date +%s`
            if [ $[ $now - $time ] -gt 20 ];then 
                ps -fe | grep "$mqcommond" | grep -v "grep" | awk '{print $2}' | xargs kill
                rm -f $pidfile
                cmd="$mqcommond""/start"
                $cmd &
                echo "重启成功"
            fi
        else
            cmd="$mqcommond""/start"
            $cmd &
            echo "启动成功"
        fi
        
        let "i=$i + 2"
        if [ "$i" -gt  "60" ]; then
            i=0
            cmd="$basecmd"" notify/Complementary"
            $cmd
        fi
        sleep 2
    done
}

stop()
{
    `ps aux | grep "mqmonitor.sh" | grep "start" | grep -v 'grep' | awk '{print $2}' | xargs kill`
    cmd="$mqcommond""/stop"
    $cmd &
}

stopf()
{
    `ps aux | grep "mqmonitor.sh" | grep -v 'grep' | awk '{print $2}' | xargs kill`
    `ps aux | grep "$basecmd" | grep -v 'grep' | awk '{print $2}' | xargs kill`
    `rm -f "$pidfile $stopfile"`
}

status()
{
    cat "$statusfile"
}

#按照参数调度程序
case "$1" in
    'start' )
        `start` &  ;;
    'stop' )
        stop ;;
    'stopf' )
        stopf ;;
    'status' )
        status ;;
    *)
        errorInput
        exit ;;
esac