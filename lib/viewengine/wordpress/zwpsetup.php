<?php
//TODO: Refactor and clean up
define('CLOUDLESS_APP_DIR', WP_PLUGIN_DIR);
if (!defined('PACKAGEPATH')) {
    $tempPath='';
    if(defined('WP_PLUGIN_DIR'))
        $tempPath=WP_PLUGIN_DIR.'/AoiSora/';
    else if(defined('WP_CONTENT_DIR'))
        $tempPath.'/plugins/AoiSora/';
    else
        $tempPath=ABSPATH.'wp-content/plugins/AoiSora/';
    define('PACKAGEPATH',$tempPath);
}

if(is_admin()){
    add_filter('after_plugin_row','update_aoisora_load_first',10,3);
    function update_aoisora_load_first($plugin_file,$plugin_data){
        $plugin = plugin_basename(sl_file('AoiSora'));
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
    if(!file_exists(ABSPATH.'/.htaccess') || !is_writable(ABSPATH.'/.htaccess')){
        add_action('after_plugin_row_'.plugin_basename(sl_file('AoiSora')),'after_aoisora_plugin_htaccess_row', 10, 2 );
        function after_aoisora_plugin_htaccess_row($plugin_file, $plugin_data){
            echo '
<tr class="error" style=""><td colspan="3" class="" style=""><div class="" style="padding:3px 3px 3px 3px;font-weight:bold;font-size:8pt;border:solid 1px #CC0000;background-color:#FFEBE8">AoiSora requries .htaccess with the following code before #BEGIN WORDPRESS.
<pre>'.
                get_htaccess_rules().'</pre>
</div></td></tr>';
            //deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}
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
    $container=Container::instance();
    $container->add('IScriptInclude',new WpScriptIncludes());
    $container->add('ScriptIncludes',new ScriptIncludes());
    $container->add('IStyleInclude',new WpStyleIncludes());
    $container->add('StyleIncludes',new StyleIncludes());
    $container->add('IOptions','WpOptions','class');

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
			include_once($app."/".strtolower("app/Views/components/$component/$component.php"));
		else
			include_once(WP_PLUGIN_DIR."/$app/".strtolower("app/Views/components/$component/$component.php"));
		if(!$params)
			$params=array();
        /**
         * $c ViewComponent
         */
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
		$scripts=Html::getFooterScripts();
		$scripts+=HtmlHelper::getFooterScripts();
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
	function initiate_editor($id,$content){
        global $wp_version;
        if(version_compare($wp_version,'3.3','>=')){
            wp_editor($content,$id);
            return 'new';
        }else{
    		wp_tiny_mce(false,array("editor_selector" => $id));
            return 'old';
        }
	}
	global $hooks;
	$hooks=array('init','admin_init','admin_menu','set_plugin_has_updates'=>'update_option__transient_update_plugins','template_redirect');
	foreach($hooks as $key => $hook)
		if(is_numeric($key))
			Hook::registerHandler($hook,'wp_hook_handler');
		else
			Hook::registerHandler($key,'wp_hook_handler');	
	global $viewsections;
	$viewsections=array('print_styles'=>'wp_print_styles','print_scripts'=>'wp_print_scripts',
					'admin_print_scripts','admin_print_styles',
					'footer'=>'wp_footer','head'=>'wp_head','admin_head','admin_footer','wp_print_scripts','wp_footer','wp_print_styles');
	foreach($viewsections as $key => $section)
		if(is_numeric($key))
			View::registerHandler($section,'wp_section_handler');
		else
			View::registerHandler($key,'wp_section_handler');
	
	global $filters;
	$filters=array('query_vars','http_request_args','rewrite_rules_array','list_pages','rewrite_rules_array','rewrite_rules_array','set_plugin_has_updates'=>'pre_set_site_transient_update_plugins');
	foreach($filters as $key => $filter)
		if(is_numeric($key))
			Filter::registerHandler($filter,'wp_filter_handler');
		else
			Filter::registerHandler($key,'wp_filter_handler');
	
	Shortcode::registerHandler('add_shortcode');
	
	function wp_hook_handler($hook,$callback,$priority=100,$params=1){
		global $hooks;
		$newhook=array_key_exists_v($hook,$hooks);
		if($newhook)
			add_action($newhook,$callback,$priority);
		else
			add_action($hook,$callback,$priority);
	}
	
	function wp_filter_handler($filter,$callback,$priority=100,$params=1){
		global $filters;
		$newfilter=array_key_exists_v($filter,$filters);
		if($newfilter)
			add_action($newfilter,$callback,$priority,$params);		
		else
			add_action($filter,$callback,$priority,$params);		
	}
	function wp_section_handler($section,$callback,$priority=100,$params=1){
		global $viewsections;
		$newsection=array_key_exists_v($section,$viewsections);
		if($newsection)
			add_action($newsection,$callback,$priority,$params);
		else
			add_action($section,$callback,$priority,$params);
	}
    function wp_section_handler_run($section, $params = array()) {
        global $viewsections;
        $section= ($newsection = array_key_exists_v($section,$viewsections)) ? $newsection: $section;
        if ($params)
            call_user_func_array('do_action',array($section,$params));
        else
            call_user_func_array('do_action',array($section));
    }
	function action_url($class,$action,$partial=false){
		$action=strtolower($action);
		if(!is_string($class))
			$class=get_class($class);
		$class=strtolower($class);
        if($partial)
            return '/'.$class.'/'.$action;
		return site_url('/'.$class.'/'.$action);
	}
	function adminURL($controller,$action,$query=false){
		$url="admin.php?page=$controller&action=$action";
		if($query)
			$url.="&".$query;
		return admin_url($url);
	}
add_action('wp','aois_add_global_ctr_act');

function aois_add_global_ctr_act(){
    $ctrl=array_key_exists_v('controller',Communication::getQueryString());
	$action=array_key_exists_v(FRONTEND_ACTIONKEY,Communication::getQueryString());
    BaseController::setUpRouting($ctrl,$action);
}

function aoisora_loaded(){
    do_action('aoisora-loaded');
}

/**
 * Tells WP to call funtion that writes htaccess rules upon activation
 */
register_activation_hook(sl_file('AoiSora'), 'setup_htaccess_rules');
function setup_htaccess_rules(){
    $htaccessrules=get_htaccess_rules();
    $path=get_htaccess_rules_path();
    if(is_writable($path)){
        $temp=file_get_contents($path);
        $fh=fopen($path,'w');
        if(strpos($temp,'PHPMVC')!==FALSE){
            $htaccessrules=str_replace('$1','\$1',$htaccessrules);
            $htaccessrules=str_replace('$2','\$2',$htaccessrules);
            $temp=preg_replace("/\# BEGIN PHPMVC.*\# END PHPMVC/s",$htaccessrules,$temp);
        }else
            fwrite($fh,$htaccessrules);
        fwrite($fh,$temp);
        fclose($fh);
    }
}