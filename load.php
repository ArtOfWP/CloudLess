<?php
function load($dir){
//    echo '<ul style="background-color: #FFF">';
	$handle = opendir($dir);
	while(false !== ($resource = readdir($handle))) {
		if($resource!='.' && $resource!='..'){
//            echo '<li>';
            
			if(is_dir($dir.$resource)){
//                echo "<b>$dir$resource</b>";
				load($dir.$resource.'/');
            }else{
//                echo "<em>$dir$resource</em>";
			 	include($dir.$resource);
            }
//            echo '</li>';
		}
	}
	closedir($handle);
//    echo '</ul>';
}
function loadApp($dir){
	if(is_dir($dir.'/app/core/'))
		load($dir.'/app/core/');
	if(is_dir($dir.'/app/controllers/'))
		load($dir.'/app/controllers/');
//	if(is_dir($dir.'/app/views/widgets'))	
//		load($dir.'/app/views/widgets/');
}
function loadAoiSora(){
	include(PACKAGEPATH.'config.php');
	include(PACKAGEPATH.'lib/Route.php');
	include(PACKAGEPATH.'lib/Debug.php');
	load(PACKAGEPATH.'lib/helpers/');
	load(PACKAGEPATH.'lib/core/');
	load(PACKAGEPATH.'lib/events/');
//	include(PACKAGEPATH.'lib/filters/IFilter.php');
//	include(PACKAGEPATH.'lib/filters/SecurityFilter.php');
	load(PACKAGEPATH.'lib/controllers/');
	load(PACKAGEPATH.'lib/views/');
	
	if(VIEWENGINE=='wordpress'){
		load(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/');//.VIEWENGINE.'.php');
	}
	//include(PACKAGEPATH.'AoiSoraApp.php');
}