<?php
namespace CloudLess;
use CLMVC\Core\UniversalClassLoader;

function register_autoloading() {
    $classLoader = UniversalClassLoader::instance();
    $classLoader->registerNamespaces(array('CLMVC' => __DIR__.'/lib'));
    $classLoader->register();
}
