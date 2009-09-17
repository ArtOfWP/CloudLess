<?php
define('PREROUTE',true);
define('LOADAPPS',true);
global $loadviewengine;
$loadviewengine='WordPress';
echo file_exists('../../../wp-load.php');
require_once('../../../wp-load.php');
require_once('init.php');

$success=Route::reroute();
		if($success){
			header("Status: 200");
			header($_SERVER["SERVER_PROTOCOL"]." 200 Ok",true,200);}
		else{
			header("Status: 404");
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
		}
?>