<?php
define('PREROUTE',true);
global $loadviewengine;
$loadviewengine='WordPress';
require_once('../../../wp-load.php');
require_once('init.php');
$success=Route::reroute();

if(!$success){
	header("Status: 404");
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
}
exit();
?>