<?php
if (!defined('CLMVC_FILE_PREFIX')) {
    define('CLMVC_FILE_PREFIX', __DIR__ . '/lib');
}
include CLMVC_FILE_PREFIX . '/Core/ClassLoader.php';
$classLoader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$classLoader->registerNamespaces(array(
    'CLMVC'      => CLMVC_FILE_PREFIX
));

$classLoader->register();
global $clmvc_loader;
$clmvc_loader = $classLoader;