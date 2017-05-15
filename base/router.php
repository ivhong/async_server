<?php
namespace base;
class router{
    public static function run($path, $dir = 'controller', $argv=[]){
        try{
            $t = explode('/', $path);
            $controller = ucfirst(strtolower($t[0])) .  'Controller';

            if(isset($t[1])){
                $action = $t[1];
            }else{
                $action = 'index';
            }
            $action = 'action'.ucfirst(strtolower($action));
            $classname = $dir.'\\'.$controller;
            $class = new $classname;
            if( !method_exists($class, $action) ){
                die($classname . '缺少方法：'.$action);
            }

            $class->$action($argv);
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }
    }
}