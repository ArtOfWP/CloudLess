<?php

require_once('config.php');
require_once('load.php');

loadAoiSora();

global $db;
$db = new MySqlDatabase();

//add_action('plugins_loaded','close_connection');
function close_connection(){
//	echo "<h1>TEST</h1>";	
	global $db;
	$db->close();
}


if(defined('LOADAPPS') && LOADAPPS){
	loadApplications();
}
global $aoisoraloaded;
$aoisoraloaded=true;
?>