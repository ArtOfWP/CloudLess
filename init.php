<?php

require_once('config.php');
require_once('load.php');

loadAoiSora();

global $db;
$db = new MySqlDatabase();

if(defined('LOADAPPS') && LOADAPPS){
	loadApplications();
}
?>