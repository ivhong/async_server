<?php

namespace library;

class Curl {

    /**
     * @note   get方式请求
     * @access public static
     * @author zhangchong <zhangchong@social-touch.com>
     * @date   2015/8/19 17:08
     * @param  string $url 请求的地址
     * @return
     * */
    public static function get($url, $time = 5, $other=false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time); //在发起连接前等待的时间，如果设置为0，则无限等待
        curl_setopt($ch, CURLOPT_TIMEOUT, $time); // 设置cURL允许执行的最长秒数
        $tmp = curl_exec($ch);
        $info= curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($other){
            return [$tmp, $info, $error];
        }
        
        return $tmp;
    }

    /**
     * @note   post方式请求
     * @access public static
     * @author zhangchong <zhangchong@social-touch.com>
     * @date   2015/8/19 17:08
     * @param  string $url    请求的地址
     * @param  array  $params 请求的参数
     * @param  array  $header 请求的头信息
     * @param  int    $time   请求的超时时间 秒
     * @return
     * */
    public static function post($url, $params, $header = array(), $time = 5, $other=false) {
        if (is_array($params))
            $paramsStr = http_build_query($params);
        else
            $paramsStr = $params;
        $paramsStr = htmlspecialchars_decode($paramsStr);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time); //在发起连接前等待的时间，如果设置为0，则无限等待
        curl_setopt($ch, CURLOPT_TIMEOUT, $time); // 设置cURL允许执行的最长秒数
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsStr);
        if (count($header) > 0)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $returnValue = curl_exec($ch);
        $info= curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($other){
            return [$returnValue, $info, $error];
        }
        
        return $returnValue;
    }

    public static function _( $url , $params = array(), $method = 'GET' , $multi = FALSE, $extheaders = array(), $sslv = FALSE,$opts=[])
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        if($ci===false){
            throw new \Exception('curl init failure',50010);
        }
        curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 10);//在发起连接前等待的时间，如果设置为0，则无限等待
        curl_setopt($ci, CURLOPT_TIMEOUT, 20);// 设置cURL允许执行的最长秒数
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);//以文件流的形式返回，而不是直接输出
        curl_setopt($ci, CURLOPT_HEADER, FALSE);//启用时会将头文件的信息作为数据流输出
        if(stripos($url,"https://")!==FALSE){//公众平台调整SSL安全策略
            if($sslv){
                curl_setopt($ci, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);//禁用后cURL将终止从服务端进行验证。使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE。
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = new CURLFile(realpath($file));
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                    }elseif(is_string($params)){
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                    }else{
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }
        curl_setopt_array($ci, $opts);
        $response = curl_exec($ci);
        
        $curlinfo = curl_getinfo($ci);
        static::log([$url, $params, $response, $curlinfo]);
        curl_close ($ci);
        return $response;
    }
    
    static public function log($msg){
        if( !is_string($msg) ){
            $msg = var_export($msg, 1);
        }
        
        $msg = '[ '.date('Y-m-d H:i:s').' ]' . $msg;
        error_log($msg, 3, __DIR__.'/../runtime/curl.log');
    }
}
