<?php
spl_autoload_register( 'autoload' ); 
function autoload($class){
    $file = BASE_PATH . '/'. str_replace('\\', '/', $class) . '.php';
    if( !file_exists($file) ){
        throw new \Exception($file . ' not find.');
    }
    include( $file );
}