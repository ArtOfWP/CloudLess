<?php
/**
 * Handles rerouting that comes from htaccess redirects
 */
define('PREROUTE',true);
//include('../../../wp-load.php');
require_once 'AoiSora.php';
$success=Route::reroute();
if(!$success){
	header("Status: 404");
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
	echo "<h1>404 Not Found</h1>";
}