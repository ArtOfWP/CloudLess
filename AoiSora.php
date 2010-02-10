<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/wpdk
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 10.1
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora
define('PACKAGEPATH',dirname(__FILE__).'/');
require('init.php');
if(is_admin()){
	add_filter('after_plugin_row','update_load_first',10,3);
	function update_load_first($plugin_file,$plugin_data){
		$plugin = plugin_basename(__FILE__);
		$active = get_option('active_plugins');
		if ( $active[0] == $plugin)
			return;
		$place=array_search($plugin, $active);
		if($place===FALSE)
			return;
		array_splice($active, $place, 1);
		array_unshift($active, $plugin);
		update_option('active_plugins', $active);
	}
}

?>