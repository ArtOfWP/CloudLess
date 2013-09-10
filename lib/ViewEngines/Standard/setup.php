<?php
use CLMVC\Controllers\BaseController;
use CLMVC\Controllers\Render\RenderingEngines;
use CLMVC\Events\Filter;
use CLMVC\Events\Hook;
if (!defined('CLMVC_CACHE_PATH'))
    define('CLMVC_CACHE_PATH', ABSPATH . '/cache/');
$container = CLMVC\Core\Container::instance();
$container->add('CLMVC\\Interfaces\\IScriptInclude',new CLMVC\ViewEngines\Standard\CLMVCScriptIncludes());
$container->add('CLMVC\\Interfaces\\IStyleInclude',new CLMVC\ViewEngines\Standard\CLMVCStyleIncludes());
$container->add('CLMVC\\Interfaces\\IOptions','CLMVC\\ViewEngines\\Standard\\BIOptions','class');
$container->add('CLMVC\\Interfaces\\IOption','CLMVC\\ViewEngines\\Standard\\BIOption','class');

RenderingEngines::registerEngine('jade', 'Jade\\Jade');
Filter::register('view-tags', 'clmvc_setup_default_tags');

/**
 * @param array $tags
 * @param BaseController $controller
 * @return array
 */
function clmvc_setup_default_tags($tags, $controller) {
    $bag = $controller->getBag();
    $tags[] = array('{{title}}', Filter::run('title', array($bag['title'])));
    $tags[] = array('{{stylesheets}}', implode("\n", Filter::run('stylesheets-frontend', array(array()))));
    $tags[] = array('{{javascript_footer}}', implode("\n", Filter::run('javascripts-footer-frontend', array(array()))));
    $tags[] = array('{{javascript_head}}', implode("\n", Filter::run('javascripts-head-frontend', array(array()))));
    return $tags;
}
function clmvc_app_url($app, $url) {
    return $url;
}

function aoisora_loaded() {
    Hook::run('aoisora-loaded');
}