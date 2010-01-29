<?php
function load($dir){
	$handle = opendir($dir);
	while(false !== ($resource = readdir($handle))) {
		if($resource!='.' && $resource!='..'){
			if(is_dir($dir.$resource))
				load($dir.$resource.'/');
			else
			 	include_once($dir.$resource);
		}
	}
	closedir($handle);
}
function loadApp($dir){
	if(is_dir($dir.'/app/core/'))
		load($dir.'/app/core/');
//	load($dir.'/app/core/wordpress/');
//	load($dir.'/app/core/domain/');
	if(is_dir($dir.'/app/controllers/'))
		load($dir.'/app/controllers/');
	if(is_dir($dir.'/app/views/widgets'))	
		load($dir.'/app/views/widgets/');
}
function loadAoiSora(){
	include(PACKAGEPATH.'config.php');
	include(PACKAGEPATH.'lib/Route.php');
	include(PACKAGEPATH.'lib/Debug.php');
	load(PACKAGEPATH.'lib/helpers/');	
	load(PACKAGEPATH.'lib/core/');
	load(PACKAGEPATH.'lib/filters/');	
	load(PACKAGEPATH.'lib/controllers/');

	global $loadviewengine;
	if($loadviewengine)
		load(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/');//.VIEWENGINE.'.php');
	include(PACKAGEPATH.'AoiSoraApp.php');
}
function loadApplications(){
	$appsSettings=AoiSoraSettings::getApplications();
	global $apps;
	$apps=array();
	if($appsSettings)
		foreach($appsSettings as $app => $path){
			$apps[]=$path;
			loadApp($path);
		}
}
?>