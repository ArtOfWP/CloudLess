<?php
function load($dir){
    $files=loadFiles($dir);
    sort($files);
    foreach($files as $file) {
        if (file_exists($file) && !is_dir($file))
            include $file;
    }
}
function loadFiles($dir){
    $files=array();
    $handle = opendir($dir);
    while(false !== ($resource = readdir($handle))) {
        if($resource!='.' && $resource!='..'){
            if(is_dir($dir.$resource)){
                $files=$files+loadFiles($dir.$resource.'/');
            }else{
                $files[$resource]=$dir.$resource;
            }
        }
    }
    closedir($handle);
    return $files;
}
function loadApp($dir){
	if(is_dir($dir.'/app/core/'))
		load($dir.'/app/core/');
	if(is_dir($dir.'/app/controllers/'))
		load($dir.'/app/controllers/');
}
function loadAoiSora(){
	include(PACKAGEPATH.'config.php');
	include(PACKAGEPATH.'lib/Route.php');
	include(PACKAGEPATH.'lib/Debug.php');
    load(PACKAGEPATH.'lib/interfaces/');
	load(PACKAGEPATH.'lib/helpers/');
	load(PACKAGEPATH.'lib/core/');
	load(PACKAGEPATH.'lib/events/');
	load(PACKAGEPATH.'lib/controllers/');
	load(PACKAGEPATH.'lib/views/');
	
	if(VIEWENGINE=='wordpress'){
		load(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/');
	}
}