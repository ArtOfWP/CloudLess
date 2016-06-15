<?php
if (!defined('CLMVC_FILE_PREFIX')) {
    define('CLMVC_FILE_PREFIX', __DIR__.'/lib');
}
define('VIEWENGINE', 'WordPress');
define('PACKAGE_PATH', WP_PLUGIN_DIR.'/'.basename(__DIR__).'/');

require_once CLMVC_FILE_PREFIX . '/Core/UniversalClassLoader.php';
global $classLoader;
$classLoader = CLMVC\Core\UniversalClassLoader::instance();
require_once (PACKAGE_PATH.'/autoloader.php');
\CloudLess\register_autoloading();
require_once(PACKAGE_PATH.'/functions.php');
if (!defined('CLOUDLESS_CONFIG')) {
    if (file_exists(PACKAGE_PATH.'/config.php'))
        include(PACKAGE_PATH.'/config.php');
} else
    include CLOUDLESS_CONFIG;
if (file_exists(PACKAGE_PATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php'))
    require PACKAGE_PATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php';
else {
    trigger_error(sprintf('Cannot find viewengine file %s', PACKAGE_PATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php'), E_USER_ERROR);
}