<?php
spl_autoload_register('vnad_autoload');
function vnad_autoload($class) {
    $root=dirname(__FILE__).'/includes/classes/';
    vnad_autoload_root($root, $class);
}
function vnad_autoload_root($root, $class) {
    $slash=substr($root, strlen($root)-1);
    if($slash!='/' && $slash!='\\') {
        $root.='/';
    }
    $name=str_replace(vnad_PLUGIN_PREFIX, '', $class);
    if(strpos($class, vnad_PLUGIN_PREFIX)===FALSE) {
        //autoload only plugin classes
        return;
    }

    $h=opendir($root);
    while($file=readdir($h)) {
        if(is_dir($root.$file) && $file != '.' && $file != '..') {
            vnad_autoload_root($root.$file, $class);
        } elseif(file_exists($root.$name.'.php')) {
            include_once($root.$name.'.php');
        } elseif(file_exists($root.$class.'.php')) {
            include_once($root.$class.'.php');
        }
    }
}
function vnad_include_php($root) {
    $h=opendir($root);
    $slash=substr($root, strlen($root)-1);
    if($slash!='/' && $slash!='\\') {
        $root.='/';
    }

    while($file=readdir($h)) {
        if(is_dir($root.$file) && $file != '.' && $file != '..'){
            vnad_include_php($root.$file);
        } elseif(strlen($file)>5) {
            $ext='.php';
            $length=strlen($ext);
            $start=$length*-1; //negative
            if(strcasecmp(substr($file, $start), $ext)==0) {
                include_once($root.$file);
            }
        }
    }
}