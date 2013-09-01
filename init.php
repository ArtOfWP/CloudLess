<?php
/**
 * Loads and initiates AoiSora and mysql db layer.
 * @global MySqlDatabase $db
 */
if (!defined('CLOUDLESS_CONFIG'))
    include(PACKAGEPATH.'config.php');
else
    include CLOUDLESS_CONFIG;
require PACKAGEPATH . 'lib/Helpers/ArraysHelper.php';
include(PACKAGEPATH . 'clmvc-autoloader.php');

/*
global $db;
$db = new MySqlDatabase();*/