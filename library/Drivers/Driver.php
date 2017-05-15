<?php
namespace library\Drivers;

 abstract class Driver{
    //是否重新发送
    public $resend = false;
    //对应的队列名
    public $keyname = '';
    //重新发送延时
    public $resend_time = 0;
    /**
     * 设置要处理的数据
     *@param int $id 数据库主键(典型包) 或者 $data 发送数据(非典型包)
     */
    abstract function setData($id);
    
    /**
     * 处理数据接口
     *@return status 1 表示正常，2 表示异常，只有2的状态才能重新发送 
    */
    abstract function deal();
    
    /**
     * 测试发送接口
     * 发送失败返回false 发送成功 返回true
     */
    abstract function testDeal();
}