<?php
if (!defined('CLMVC_FILE_PREFIX')) {
    define('CLMVC_FILE_PREFIX', __DIR__.'/lib');
}
define('VIEWENGINE', 'WordPress');
define('PACKAGEPATH', WP_PLUGIN_DIR.'/'.basename(__DIR__).'/');

require_once CLMVC_FILE_PREFIX . '/Core/UniversalClassLoader.php';
global $classLoader;
$classLoader = CLMVC\Core\UniversalClassLoader::instance();
require_once (PACKAGEPATH.'/autoloader.php');
\CloudLess\register_autoloading();
require_once(PACKAGEPATH.'/functions.php');
if (!defined('CLOUDLESS_CONFIG')) {
    if (file_exists(PACKAGEPATH.'/config.php'))
        include(PACKAGEPATH.'/config.php');
} else
    include CLOUDLESS_CONFIG;
if (file_exists(PACKAGEPATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php'))
    require PACKAGEPATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php';
else {
    trigger_error(sprintf('Cannot find viewengine file %s', PACKAGEPATH.'/lib/ViewEngines/'.ucfirst(VIEWENGINE).'/setup.php'), E_USER_ERROR);
}