<?php
require dirname(__DIR__) . '/vendor/autoload.php';
/**
 * @param $class
 * @return string
 */
function get_ns($class) {
    return substr($class,0,strrpos($class,'\\'));
}

$root_dir=dirname(__DIR__);
$classLoader = \CLMVC\Core\UniversalClassLoader::instance();
$classLoader->registerNamespaces(['tests' => __DIR__]);
$classLoader->register();