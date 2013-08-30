<?php
/**
 * Loads directories files are included
 * @param sting $dir
 */
function load($dir){
    $files=loadFiles($dir);
    sort($files);
    foreach($files as $file) {
        if (file_exists($file) && !is_dir($file))
            include $file;
    }
}

/**
 * Traverses a directory and returns all found files.
 * @param $dir
 * @return array
 */
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

/**
 * Loads an application
 * @param string $dir
 */
function loadApp($dir){
	if(is_dir($dir.'/app/core/'))
		load($dir.'/app/core/');
	if(is_dir($dir.'/app/controllers/'))
		load($dir.'/app/controllers/');
}

/**
 * Loads required files, Loads wordpress viewengine if defined.
 */
function loadAoiSora(){
    if (!defined('CLOUDLESS_CONFIG'))
        include(PACKAGEPATH.'config.php');
    else
        include CLOUDLESS_CONFIG;
	include(PACKAGEPATH . 'lib/Route.php');
	include(PACKAGEPATH.'lib/Debug.php');
    load(PACKAGEPATH.'lib/interfaces/');
	load(PACKAGEPATH.'lib/helpers/');
	load(PACKAGEPATH.'lib/core/');
	load(PACKAGEPATH.'lib/events/');
	load(PACKAGEPATH.'lib/controllers/');
	load(PACKAGEPATH.'lib/views/');
	if(VIEWENGINE=='wordpress'){
		load(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/');
	} else {
        load(PACKAGEPATH.'lib/viewengine/standard/');
    }
}