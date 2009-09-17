<?php
	if(!defined('WP_PLUGIN_DIR'))
		define('APPPATH',dirname(__FILE__).'/');
	else
		define('APPPATH',WP_PLUGIN_DIR);
	Debug::Value('APPPATH',APPPATH);
	define('LIB','/app/');
	define('DOMAIN',LIB.'domain/');
	define('VIEWS',LIB.'views/');
	define('CONTROLLERS',LIB.'/controllers/');
	if(!defined('PREROUTE'))
		define('POSTPATH',WP_PLUGIN_URL);
	if(defined('PREROUTE')){
		define('CONTROLLERKEY','controller');
		define('ACTIONKEY','action');
	}else{
		define('CONTROLLERKEY','page');
		define('ACTIONKEY','action');
	}
	function render_views(){
		global $wp_query;
		if( !isset($wp_query->query_vars['controller']) )
			return;
		$controller=array_key_exists_v('controller',$wp_query->query_vars);
		$action=array_key_exists_v('action',$wp_query->query_vars);
		if($controller && $action)
			Route::rerouteToAction($controller,$action);
		else if($controller)
			Route::rerouteToController($controller);
		else{
			die("Controller: <strong>" . $wp_query->query_vars['controller'] . "</strong> page: <strong>" . $wp_query->query_vars['action'] . "</strong>");
		}
		$args=apply_filters('render_from_template', array('controller'=> $controller,'action'=>$action,'template'=>TEMPLATEPATH));
		Debug::Value('Template',$args);
		include(TEMPLATEPATH.$args['template']);
		exit;
	}
	if(!defined('PREROUTE'))
		add_action('template_redirect','render_views');
?>