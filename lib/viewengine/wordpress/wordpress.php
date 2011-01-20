<?php
	global $table_prefix;
	global $db_prefix;
	$db_prefix=$table_prefix.'aoisora_';
Debug::Message('Loaded wordpress viewengine');
	define('PACKAGEURL',WP_PLUGIN_URL.'/AoiSora/');
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
		if(is_admin() && !array_key_exists('controller',$_GET)){
			define('CONTROLLERKEY','page');
			define('ACTIONKEY','action');
			define('FRONTEND_ACTIONKEY','anaction');			
		}
		else{
			define('CONTROLLERKEY','controller');			
			define('FRONTEND_ACTIONKEY','anaction');
			define('ACTIONKEY','action');
		}
	}
	$wud=wp_upload_dir();
	define('UPLOADS_DIR',$wud['basedir'].'/');
	define('UPLOADS_URI',$wud['baseurl'].'/');
	function register_aoisora_query_vars($public_query_vars) {
		$public_query_vars[] = "controller";
		$public_query_vars[] = "action";
		$public_query_vars[] = "anaction";		
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
	}
	add_filter('query_vars', 'register_aoisora_query_vars');
	
	function viewcomponent($app,$component,$params=false){
		if(strpos($app,WP_PLUGIN_DIR)!==false || strpos($app,':'))
			include_once($app."/".strtolower("app/views/components/$component/$component.php"));		
		else
			include_once(WP_PLUGIN_DIR."/$app/".strtolower("app/views/components/$component/$component.php"));
		if(!$params)
			$params=array();
		$c = new $component($params);
		$c->render();
	}
	class ViewEngine{
		static function createOption($name){
			return new WpOption($name);
		}
		static function createSecurity(){
			return WpSecurity::instance();
		}
	}
	if(!is_admin())
		add_action('wp_footer', 'aoisora_script_footer');	
	else
		add_action('admin_footer', 'aoisora_script_footer');

	function aoisora_script_footer() {
		$scripts=HtmlHelper::getFooterScripts();
		if(empty($scripts))
			return;
		echo "<script type=\"text/javascript\">";
		echo implode(' ',$scripts);
		echo "</script>";
	}
	if(!function_exists('get_site_url')){
		function get_site_url(){
			return get_bloginfo('url');
		}
	}
	function get_akismet_key(){
		return akismet_get_key();
	}
	function get_htaccess_rules_path(){
		return ABSPATH.'/.htaccess';
	}
	function get_htaccess_rules(){
		$url=strtolower(get_site_url());
		$siteurl=strtolower(get_bloginfo( 'wpurl'));
		$path=str_replace($url,'',$siteurl);
	$htaccess_rules=
'# BEGIN PHPMVC
	<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /'.$path.'
	RewriteCond %{REQUEST_METHOD} !GET
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_URI} /(create|delete|update) [NC]
	RewriteRule ^(.*)/(.*)$ '.$path.'/wp-content/plugins/AoiSora/preroute.php?controller=$1&action=$2 [L]
</IfModule>
# END PHPMVC
';
		return $htaccess_rules;
	}
	function initiate_editor($class){
		wp_tiny_mce(false,array("editor_selector" => $class));
	}
	add_action('plugins_loaded','aoisora_loaded');
	function aoisora_loaded(){
		do_action('aoisora_loaded');
	}
	ViewHelper::registerViewSectionHandler('admin_head','wp_admin_section_handler');
	ViewHelper::registerViewSectionHandler('admin_footer','wp_admin_section_handler');
	ViewHelper::registerViewSectionHandler('admin_print_scripts','wp_admin_section_handler');
	ViewHelper::registerViewSectionHandler('admin_print_styles','wp_admin_section_handler');
	function wp_admin_section_handler($section,$callback){
		add_action($section,$callback);
	}
	function adminURL($controller,$action,$query=false){
		$url="admin.php?page=$controller&action=$action";
		if($query)
			$url.="&".$query;
		admin_url($url);
	}