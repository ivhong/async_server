<?php
namespace library;

class Request{
    public static function getVar($request, $key='', $default=''){
        static $requests = [];
        if( empty($requests[$request]) ){
            switch ($request){
                case 'post':
                    $var = $_POST;
                    break;
                case 'get':
                    $var = $_GET;
                    break;
                case 'request':
                    $var = $_REQUEST;
                    break;
                case 'cookie':
                    $var = $_COOKIE;
                    break;
            }
            
            $requests[$request] = self::filterVar($var);
        }
        
        if($key){
            return empty($requests[$request][$key]) ? $default : $requests[$request][$key];
        }
        
        return $requests[$request];
    }
    
    public static function post($key='', $default=''){
        return self::getVar('post', $key, $default);
    }
    
    public static function get($key='', $default=''){
        return self::getVar('get', $key, $default);
    }
    
    public static function request($key='', $default=''){
        return self::getVar('request', $key, $default);
    }
    
    public static function cookie($key='', $default=''){
        return self::getVar('cookie', $key, $default);
    }
    
    public static function filterVar($var){
        $var = array_map('trim', $var);
        return $var;
    }
}