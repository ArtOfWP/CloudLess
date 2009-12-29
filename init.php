<?php

require_once('config.php');
require_once('load.php');

loadAoiSora();

global $db;
$db = new MySqlDatabase();

//add_action('plugins_loaded','close_connection');
function close_connection(){
	global $db;
	$db->close();
}


if(defined('LOADAPPS') && LOADAPPS){
	loadApplications();
}
global $aoisoraloaded;
$aoisoraloaded=true;
?>