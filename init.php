<?php
/**
 * Loads and initiates AoiSora and mysql db layer.
 * @global MySqlDatabase $db
 */

include(PACKAGEPATH . 'clmvc-autoloader.php');/*
if (!defined('CLOUDLESS_CONFIG'))
    if (file_exists(PACKAGEPATH.'config.php'))
        include(PACKAGEPATH.'config.php');
else
    include CLOUDLESS_CONFIG;*/
require PACKAGEPATH . 'lib/Helpers/ArraysHelper.php';
if (file_exists(PACKAGEPATH . 'lib/ViewEngines/' . ucfirst(VIEWENGINE) . '/setup.php'))
    require PACKAGEPATH . 'lib/ViewEngines/' . ucfirst(VIEWENGINE) . '/setup.php';
else {
    trigger_error(sprintf('Cannot find viewengine file %s', PACKAGEPATH . 'lib/ViewEngines/' . ucfirst(VIEWENGINE) . '/setup.php'), E_USER_ERROR);
}
/*
global $db;
$db = new MySqlDatabase();*/