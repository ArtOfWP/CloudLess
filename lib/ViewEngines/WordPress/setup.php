<?php
//TODO: Refactor and clean up
use CLMVC\Controllers\BaseController;
use CLMVC\Controllers\Render\RenderingEngines;
use CLMVC\Core\Debug;
use CLMVC\Core\Http\Route;
use CLMVC\Core\Http\Routes;
use CLMVC\Events\Filter;
use CLMVC\Events\Hook;
use CLMVC\Events\View;
use CLMVC\Helpers\Html;
use CLMVC\Views\Shortcode;

define('CLOUDLESS_APP_DIR', WP_PLUGIN_DIR);

RenderingEngines::registerEngine('php', 'CLMVC\\Controllers\\Render\Engines\\PhpRenderingEngine');
//Filter::register('view-tags', 'clmvc_setup_default_tags');

/**
 * @param array $tags
 * @param BaseController $controller
 * @return array
 */
function clmvc_setup_default_tags($tags, $controller) {
    $bag = $controller->getBag();
    $tags['title'] = Filter::run('title', array($bag['title']));
    $tags['stylesheets'] = implode("\n", Filter::run('stylesheets-frontend', array(array())));
    $tags['javascript_footer'] = implode("\n", Filter::run('javascripts-footer-frontend', array(array())));
    $tags['javascript_head'] = implode("\n", Filter::run('javascripts-head-frontend', array(array())));
    return $tags;
}

function sl_file($file,$isPlugin=true) {
    if($isPlugin)
        return CLOUDLESS_APP_DIR.'/'.$file.'/'.$file.'.php';
    return CLOUDLESS_APP_DIR.'/'.$file;
}

if (!defined('PACKAGEPATH')) {
    $tempPath='';
    if(defined('WP_PLUGIN_DIR'))
        $tempPath=WP_PLUGIN_DIR.'/AoiSora/';
    else if(defined('WP_CONTENT_DIR'))
        $tempPath = WP_CONTENT_DIR . '/plugins/AoiSora/';
    else
        $tempPath=ABSPATH.'wp-content/plugins/AoiSora/';
    define('PACKAGEPATH',$tempPath);
}

function clmvc_app_url($app, $url) {
    return $url;
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
}
	global $table_prefix;
	global $db_prefix;
	$db_prefix=$table_prefix.'aoisora_';
	Debug::Message('Loaded WordPress ViewEngines');
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
$container = CLMVC\Core\Container::instance();
$container->add('CLMVC\\Interfaces\\IScriptInclude',new CLMVC\ViewEngines\WordPress\WpScriptIncludes());
$container->add('CLMVC\\Interfaces\\IStyleInclude',new CLMVC\ViewEngines\WordPress\WpStyleIncludes());
$container->add('CLMVC\\Interfaces\\IOptions','CLMVC\\ViewEngines\\WordPress\\WpOptions','class');
$container->add('CLMVC\\Interfaces\\IOption','CLMVC\\ViewEngines\\WordPress\\WpOption','class');
$container->add('CLMVC\\Interfaces\\IPost','CLMVC\\ViewEngines\\WordPress\\WpPost','class');
$container->add('Routes', new Routes());
$container->add('Bag', new \CLMVC\Controllers\BaggedValues());
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

	if(!is_admin())
		add_action('wp_footer', 'aoisora_script_footer');	
	else
		add_action('admin_footer', 'aoisora_script_footer');

	function aoisora_script_footer() {
		$scripts=Html::getFooterScripts();
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



	function initiate_editor($id,$content){
        wp_editor($content,$id);
        return 'new';
	}

    add_action('wp_register_scripts', function() {
       Hook::run('scripts-register');
    });
    add_action('wp_register_style', function() {
        Hook::run('style-register');
    });
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
					'footer'=>'wp_footer','head'=>'wp_head','admin_head','admin_footer','wp_print_scripts','wp_footer','wp_print_styles', 'enqueue_scripts' => 'wp_enqueue_scripts');
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
    add_action('init', function() {
        /**
         * @var Routes $routes
         */
        $container = \CLMVC\Core\Container::instance();
        $routes = $container->fetch('Routes');
        $routes->routing();
    });
    Hook::register('template_redirect', function() {
        if (\CLMVC\Controllers\Render\RenderedContent::hasRendered()) {
            global $wp_query;
            if ($wp_query->is_404) {
                $wp_query->is_404 = false;
            }
            global $clmvc_http_code;
            http_response_code($clmvc_http_code);
            if (\CLMVC\Controllers\Render\RenderedContent::endIt()) {
                \CLMVC\Controllers\Render\RenderedContent::endFlush();
            } else {
                include clmvc_template();
                exit();
            }
        }
    });


add_filter('wp_title', function($title, $sep, $seplocation) {
    $bag = \CLMVC\Core\Container::instance()->fetch('Bag');
    if (isset($bag->title))
        return $bag->title . $sep;
    return $title . $sep;
},0, 3);

function clmvc_template() {
    return get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'cloudless.php';
}
