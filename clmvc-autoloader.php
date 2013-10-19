<?php
if (!defined('CLMVC_FILE_PREFIX')) {
    define('CLMVC_FILE_PREFIX', __DIR__ . '/lib');
}
include CLMVC_FILE_PREFIX . '/Core/ClassLoader.php';
$classLoader = Symfony\Component\ClassLoader\UniversalClassLoader::instance();
$classLoader->registerNamespaces(array(
    'CLMVC'      => CLMVC_FILE_PREFIX,
));

$classLoader->register();