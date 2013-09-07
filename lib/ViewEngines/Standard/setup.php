<?php

//use CLMVC\Core\Includes\ScriptIncludes;
//use CLMVC\Core\Includes\StyleIncludes;
use CLMVC\Events\Hook;
//use CLMVC\ViewEngines\Standard\CLMVCScriptIncludes;
//use CLMVC\ViewEngines\Standard\CLMVCStyleIncludes;

$container = CLMVC\Core\Container::instance();
$container->add('CLMVC\\Interfaces\\IScriptInclude',new CLMVC\ViewEngines\Standard\CLMVCScriptIncludes());
//$container->add('CLMVC\\Core\Includes\\ScriptIncludes',new CLMVC\Core\Includes\ScriptIncludes());
$container->add('CLMVC\\Interfaces\\IStyleInclude',new CLMVC\ViewEngines\Standard\CLMVCStyleIncludes());
//$container->add('CLMVC\\Core\Includes\\StyleIncludes',new CLMVC\Core\Includes\StyleIncludes());
//$container->add('CLMVC\\Interfaces\\IOptions','CLMVC\\ViewEngines\\Standard\\BIOptions','class');
$container->add('CLMVC\\Interfaces\\IOption','CLMVC\\ViewEngines\\Standard\\BIOption','class');

function clmvc_app_url($app, $url) {
    return $url;
}

function aoisora_loaded() {
    Hook::run('aoisora-loaded');
}