<?php
	if(!defined('WP_PLUGIN_DIR'))
		define('APPPATH',dirname(__FILE__).'/');
	else
		define('APPPATH',WP_PLUGIN_DIR);
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
		if(is_admin())
			define('CONTROLLERKEY','page');
		else
			define('CONTROLLERKEY','controller');			
		define('ACTIONKEY','action');
	}
	function register_aoisora_query_vars($public_query_vars) {
		$public_query_vars[] = "controller";
		$public_query_vars[] = "action";
		return $public_query_vars;
	}
	function render_views(){
		global $wp_query;
		if( !isset($wp_query->query_vars[CONTROLLERKEY]) )
			return;
		$controller=array_key_exists_v(CONTROLLERKEY,$wp_query->query_vars);
		$action=array_key_exists_v(ACTIONKEY,$wp_query->query_vars);
		if($controller && $action)
			Route::rerouteToAction($controller,$action);
		else if($controller)
			Route::rerouteToController($controller);
		else{
			die("Controller: <strong>" . $wp_query->query_vars[CONTROLLERKEY] . "</strong> page: <strong>" . $wp_query->query_vars[ACTIONKEY] . "</strong>");
		}
		$args=apply_filters('render_from_template', array(CONTROLLERKEY=> $controller,ACTIONKEY=>$action,'template'=>TEMPLATEPATH));
		include(TEMPLATEPATH.$args['template']);
		exit;
	}
	if(!defined('PREROUTE'))
		add_action('template_redirect','render_views');
		
	add_filter('query_vars', 'register_aoisora_query_vars');
	function aoisora_render_title($title,$sep=" &mdash; ",$placement="left"){
		global $aoisoratitle;
		$title.=$aoisoratitle." $sep ";
		return $title;
	}
	add_filter('wp_title','aoisora_render_title',10,3);
?>