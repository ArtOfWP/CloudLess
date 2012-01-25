<?php
define('PREROUTE',true);
global $loadviewengine;
$loadviewengine='WordPress';
include('../../../wp-load.php');
//include('init.php');
$success=Route::reroute();
if(!$success){
	header("Status: 404");
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
	echo "<h1>404 Not Found</h1>";
}