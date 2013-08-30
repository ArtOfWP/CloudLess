<?php
$container=Container::instance();
$container->add('IScriptInclude',new CLMVCScriptIncludes());
$container->add('ScriptIncludes',new ScriptIncludes());
$container->add('IStyleInclude',new CLMVCStyleIncludes());
$container->add('StyleIncludes',new StyleIncludes());
$container->add('IOptions','BIOptions','class');

if(defined('PREROUTE')){
    define('CONTROLLERKEY','controller');
    define('ACTIONKEY','action');
}

function clmvc_app_url($app, $url) {
    return $url;
}

function aoisora_loaded() {
    Hook::run('aoisora-loaded');
}