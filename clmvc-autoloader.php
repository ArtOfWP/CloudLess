<?php
if (!defined('CLMVC_FILE_PREFIX')) {
    define('CLMVC_FILE_PREFIX', __DIR__.'/lib');
}
require CLMVC_FILE_PREFIX.'/Core/ClassLoader.php';
global $classLoader;
$classLoader = CLMVC\Core\UniversalClassLoader::instance();
$classLoader->registerNamespaces(array(
    'CLMVC'      => CLMVC_FILE_PREFIX,
));
$classLoader->register();
