<?php
//监听队列退出信号，当这个值出队的话，退出监听
define('REDUCESIGNAL', 'QQQQQQQQQQQQQQQQQQQQQQ');
define('MQTIMERKEY', '__MQTIMER__');
//第一遍发送失败，则重新发送，比如说curl发送失败，会触发从新发送机制
define('NOTIFYRESEND', '__NOTIFYRESEND__');

return [
    'server'=>[
        'host' => '127.0.0.1',
        'port' => '6379',
        'pass' => '0z2VQ0AGjr',
    ],
    /**
     * 队列配置
     * 配置优先级：handle（队列通用前缀）< types（类型配置）< resend_handle（重新发送配置）< specific （特殊key配置）
     */
    'keys' => [
        //通知任务队列的key
        'notify_redis_key' => [
            #队列通用前缀
            'key' => 'SmsServer_notify_redis_key',
            #队列通用配置
            'handle' => [
                #定义每个队列对应的进程执行基数
                #大于等于这个基数讲按照 ceil(vnum / 基数)vnum为队列内值的个数
                #比如说 基数等于5，那么队列里有5个待解决数值时，有1个进程，有6 ~ 10个时，有2个进程处理，有11 ~ 15个是有3个进程处理，以此类推
                'doScritpNum' => 50,
                #处理进程最大数量
                'doScritpNumMax' => 10,
                #超过报警时间间隔,描述
                'doAlertTime' => 600,
            ],
            #最大辅助进程数量
            'max_auxiliary_child' => 100,
            
            #特殊的键值需要特殊的配置
            'specific' => [
                'SmsServer_notify_redis_key_curl32'=>[
                    'doScritpNum' => 5,
                    'doScritpNumMax' => 6,
                    'doAlertTime' => 60,
                ],
                'SmsServer_notify_redis_key_shiqusdkalert'=>[
                    'doScritpNum' => 100,
                    'doScritpNumMax' => 20,
                    'doAlertTime' => 3600,
                ]
            ],
            
            #错误重发的配置
            'resend_handle' => [
                'doScritpNum' => 200,
                'doScritpNumMax' => 5,
                'doAlertTime' => 60,
            ],
        
            //按类型设置资源费配
            'types' => [
                'sms' => [
                    'doScritpNum' => 100,
                    'doScritpNumMax' => 5,
                    'doAlertTime' => 60,
                ]
            ],
            
            #错误重发的最大辅助进程数量
            'resend_max_auxiliary_child' => 30,
        ],
        
        #对于错误的队列，重试时间(秒)
        'resettime' => 300,
        #连续错误数，如果某队列连续发送大于该数，则冻结该队列
        'maxErrorNum' => 10,
        
        #禁止补发的队列名
        'denyComplementaryKeys' => [
//            'SmsServer_notify_redis_key_curl301'
        ],
        
        'alert' => [
            'email' => 'wangchanghong@social-touch.com',
        ],
        
        #特殊的队列，用于创蓝短信渠道监听短信发送状态的队列名
        'listen_sms_status' => 'SmsServer_listensmsstatus',
    ],
];
