<?php
define('HOST','localhost');
define('DATABASE','phpmvc');
define('USERNAME','');
define('PASSWORD','');
require_once('load.php');
require_once('lib/Route.php');
require_once('lib/Debug.php');
load(dirname(__FILE__).'/lib/helpers/');
load(dirname(__FILE__).'/lib/core/');
load(dirname(__FILE__).'/lib/controllers/');
global $db;
$db = new MySqlDatabase();

$setting = new Setting();
$setting->setKey('');
$setting->setValue('');
$setting->setApplication('');
//$db->dropTable($setting);
$db->createTable($setting);
?>