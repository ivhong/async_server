<?php
namespace base;

abstract class WebController{
    public $title = '';
    public $cssFile = [];
    public $cssScript = [];
    public $jsFile = [];
    public $jsScript = [];
    public $header = [];
    
    public function __construct() {
        $this->init();
    }
    
    abstract function init();
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    /**
     * 添加css文件
     */
    public function addCss($file){
        $this->cssFile[$file] = 1;
    }
    
    public function addCssScript($str){
        $this->cssScript[] = $str;
    }
    
    public function addJs(){
        $this->jsFile[$file] = 1;
    }
    
    public function addJsScript(){
        $this->jsScript[] = $str;
    }
    
    public function addHeader($header){
        $this->header[$header] = 1;
    }
    
    public function rander($view, $file, $main = true){
        $path = BASE_PATH . '/templates/' . $view . '/'.$file.'.php';
        
        ob_clean();
        require $path;
        $this->content = ob_get_contents();
        ob_end_clean();
        
        if($main){
            $view = BASE_PATH . '/templates/' . $view . '/main.php';
            require $view;
        }else{
            echo $this->content;
        }
    }
    
    
    public function getTitle(){
        return $this->title;
    }
    
    public function getHeader(){
        
    }
    
    public function getContent(){
        return $this->content;
    }
}
