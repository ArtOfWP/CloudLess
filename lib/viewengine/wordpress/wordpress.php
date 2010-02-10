<?php
	global $table_prefix;
	global $db_prefix;
	$db_prefix=$table_prefix.'aoisora_';
Debug::Message('Loaded wordpress viewengine');
	if(!defined('WP_PLUGIN_DIR'))
		define('APPPATH',dirname(__FILE__).'/');
	else
		define('APPPATH',WP_PLUGIN_DIR);
	if(!defined('PREROUTE'))
		define('POSTPATH',WP_PLUGIN_URL);
	if(defined('PREROUTE')){
		define('CONTROLLERKEY','controller');
		define('ACTIONKEY','action');
	}else{
		if(is_admin() && !array_key_exists('controller',$_GET))
			define('CONTROLLERKEY','page');
		else
			define('CONTROLLERKEY','controller');			
		define('ACTIONKEY','action');
	}
	function register_aoisora_query_vars($public_query_vars) {
		$public_query_vars[] = "controller";
		$public_query_vars[] = "action";
		$public_query_vars[] = "result";
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
//		$args=apply_filters('render_from_template', array(CONTROLLERKEY=> $controller,ACTIONKEY=>$action,'template'=>TEMPLATEPATH));
//		include(TEMPLATEPATH.$args['template']);
//		exit;
	}
//	if(!defined('PREROUTE') && !is_admin())
//		add_action('template_redirect','render_views');
	add_filter('query_vars', 'register_aoisora_query_vars');
	function aoisora_render_title($title,$sep=" &mdash; ",$placement="left"){
		global $aoisoratitle;
		$title= $aoisoratitle." $sep $title";
		return $title;
	}
	add_filter('wp_title','aoisora_render_title',10,3);
	function viewcomponent($app,$component,$params=false){
		include_once(WP_PLUGIN_DIR."/$app/".strtolower("app/views/components/$component/$component.php"));
		if(!$params)
			$params=array();
		$c = new $component($params);
		$c->render();
	}
	add_action('plugins_loaded','aoisora_loaded');
	function aoisora_loaded(){
		do_action('aoisora_loaded');
	}
	class ViewEngine{
		static function createOption($name){
			return new WpOption($name);
		}
		static function createSecurity(){
			return WpSecurity::instance();
		}
	}
?>