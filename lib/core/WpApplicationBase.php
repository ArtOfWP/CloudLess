<?php
abstract class WpApplicationBase{
	protected $installfrompath;
	protected $VERSION=false;
	protected $VERSION_INFO_LINK=false;
	protected $UPDATE_SITE=false;
	protected $UPDATE_SITE_EXTRA=false;	
	protected $SLUG=false;
	private $models=array();
	public $pluginname;
	public $dir;
	public $app;
	public $options;
	private $useInstall;
	private $useOptions;
	function WpApplicationBase($appName,$file,$useOptions=false,$useInstall=false,$basename=false){	
		$this->dir=dirname($file);
		$this->app=$appName;
		if($basename)
			$this->pluginname=$basename;//"$appName/$appName.php";
		else
			$this->pluginname=plugin_basename($file);//"$appName/$appName.php";		
		register_activation_hook($this->pluginname, array(&$this,'activate'));
		register_deactivation_hook($this->pluginname, array(&$this,'deactivate'));
		register_uninstall_hook($this->pluginname, array(&$this,'delete'));
		$this->installfrompath=dirname($file).'/app/core/domain/';
		$this->useInstall=$useInstall;
		$this->useOptions=$useOptions;		
		if(method_exists($this,'on_register_query_vars'))
			add_filter('query_vars', array(&$this,'register_query_vars'));


		if(is_admin()){
			add_action( 'admin_init', array(&$this,'register_settings' ));				
			if(method_exists($this,'on_plugin_page_link'))
				add_filter( 'plugin_action_links_'.$this->pluginname, array(&$this,'plugin_page_links'), 10, 2 );
			if(method_exists($this,'on_plugin_row_message'))
				add_action( 'after_plugin_row_'.$this->pluginname, array(&$this,'after_plugin_row'), 10, 2 );				
			if(method_exists($this,'on_init_admin'))
				add_action('init', array(&$this,'on_init_admin'));	
			if(method_exists($this,'on_admin_init'))
				add_action('admin_init', array(&$this,'on_admin_init'));	
			if(method_exists($this,'on_admin_menu'))
				add_action('admin_menu',array(&$this,'on_admin_menu'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));
			if($_REQUEST[$appName])
				add_action('install_plugins_pre_plugin-information',array(&$this,'version_information'));			
		}else{
			if(method_exists($this,'on_init'))
				add_action('init', array(&$this,'on_init'));			
			if(method_exists($this,'on_wp_print_styles'))
				add_action('wp_print_styles',array(&$this,'print_styles'));
			if(method_exists($this,'on_wp_print_scripts'))
				add_action('wp_print_scripts',array(&$this,'print_scripts'));
			if(method_exists($this,'on_add_page_links'))
				add_filter('wp_list_pages', array(&$this,'on_add_page_links'));	
			if(method_exists($this,'render_view_template'))
				add_filter('render_from_template',array(&$this,'render_view_template'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));
			if(method_exists($this,'on_render_footer'))
				add_action('wp_footer',array(&$this,'on_render_footer'));
		}
        add_action('update_option__transient_update_plugins', array(&$this, 'check_for_update'));		
		$this->init();
		Debug::Value($appName,$this->app);

	}
		
	private function init(){
		if(method_exists($this,'on_initialize'))
			$this->on_initialize();
		if($this->useOptions){			
			$this->options= Option::create($this->app);
			if(method_exists($this,'on_load_options'))
				$this->on_load_options();			
		}		
		if(is_admin() && method_exists($this,'on_init_update')){
			$this->on_init_update();
			if($this->UPDATE_SITE && isset($_REQUEST['action']) && 'upgrade-plugin'==$_REQUEST['action'] && isset($_REQUEST['plugin']) && urldecode($_REQUEST['plugin'])==$this->pluginname)
				add_filter('http_request_args',array(&$this,'add_update_url'),10,2);
		}
	}
	
	function add_update_url($r,$url){
			$r['headers']['Referer']=get_bloginfo('url');
			return $r;
	}
	
	function register_query_vars($public_query_vars){
		$vars=$this->on_register_query_vars();
		foreach($vars as $var)
			$public_query_vars[]=$var;
		return $public_query_vars;
	}
	function register_settings(){		
		if(method_exists($this,'on_register_settings')){
			$settings = $this->on_register_settings();
			foreach($settings as $option => $key)
				if(is_array($key))
					WpHelper::registerSettings($option,$key);
				else
					WpHelper::registerSettings($option,array($key));				
		}
		if($this->useOptions)
			WpHelper::registerSettings($this->app,array($this->app));
	}
	function after_plugin_row($plugin_file, $plugin_data){
		$display = $this->on_plugin_row_message();
		extract($display);
		echo '<tr class="'.$trclass.'" style="'.$trstyle.'"><td colspan="3" class="'.$tdclass.'" style="'.$tdstyle.'"><div class="'.$divclass.'" style="'.$divstyle.'">'.$message.'</div></td></tr>';
	}
	function plugin_page_links($links){
			$plugin_link=$this->on_plugin_page_link();
			array_unshift( $links, $plugin_link); // before other links
		return $links;
	}
	function activate(){
		$oldVersion=AoiSoraSettings::getApplicationVersion($this->app);	
		AoiSoraSettings::addApplication($this->app,$this->dir,$this->VERSION);
		if(version_compare($oldVersion<$this->VERSION,'<'))
			$this->update();
		else if(!$this->useInstall)
			AoiSoraSettings::installApplication($this->app);
		if($this->useOptions){			
			$this->options= Option::create($this->app);
			if(method_exists($this,'on_load_options')){			
				$this->on_load_options();		
			}
		}
		if(method_exists($this,'on_activate'))
			$this->on_activate();
	}
	function deactivate(){
		if(method_exists($this,'on_deactivate'))
			$this->on_deactivate();
		if(!$this->useInstall)
			AoiSoraSettings::uninstallApplication($this->app);
		if($this->useOptions){			
			$this->options= Option::create($this->app);
			$this->options->delete();
		}
		AoiSoraSettings::removeApplication($this->app);
	}
	function installed(){
		return AoiSoraSettings::installed($this->app);
	}
	public function install(){
		if(method_exists($this,'on_preinstall'))
			$this->on_preinstall();		
		Debug::Value('Install from path',$this->installfrompath);
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;
		$this->create();
		if($result)
			AoiSoraSettings::installApplication($this->app);			
		if(method_exists($this,'on_afterinstall'))
			$this->on_afterinstall();			
		return $result;
	}
	private function create(){
		global $db;
		foreach($this->models as $model){
			$m = new $model();
			$db->createTable($m);
		}
		$db->createStoredRelations();
		$db->createStoredIndexes();
	}
	public function uninstall(){
		if(method_exists($this,'on_preuninstall'))
			$this->on_preuninstall();
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;		
		$this->drop();
		if($result)
			AoiSoraSettings::uninstallApplication($this->app);
		if(method_exists($this,'on_afteruninstall'))
			$this->on_afteruninstall();	
	}
	private function drop(){
		global $db;		
		foreach($this->models as $model){
			$m = new $model();
			$db->dropTable($m);
		}
		$db->dropStoredRelations();		
	}
	function delete(){
		if($this->options)
			$this->options->delete();
	}	
	private function update(){
		if(method_exists($this,'on_update')){
			$this->on_update();
		}
		if(file_exists(trim('/',$this->dir).'/apps/updates/'.$this->VERSION.'.php'))
			require_once('apps/updates/'.$this->VERSION.'.php');
	}
	private function load($dir){
		Debug::Value('Loading directory',$dir);
		$handle = opendir($dir);
		while(false !== ($resource = readdir($handle))) {
			if($resource!='.' && $resource!='..'){
				if(is_dir($dir.$resource))
					$this->load($dir.$resource.'/');
				else{			
					$this->models[]=str_replace('.php','',$resource);				 	
					Debug::Value('Loaded',$resource);
				}
			}
		}
		closedir($handle);
	}
	function print_styles(){
		$this->loadstyles($this->on_wp_print_styles());
		
	}
	private function loadstyles($styles){
		if(isset($styles) && !empty($styles) && is_array($styles))
			foreach($styles as $name => $file){
	        $myStyleUrl = WP_PLUGIN_URL .'/'.$this->app.$file;
	        $myStyleFile = WP_PLUGIN_DIR .'/'.$this->app.$file;
	        if ( file_exists($myStyleFile) ) {
	            wp_register_style($name, $myStyleUrl);
	            wp_enqueue_style( $name);
	        }
		}		
	}
	function print_admin_styles(){
		$this->loadstyles($this->on_admin_print_styles());
		
	}
	function print_admin_scripts(){
		$scripts = $this->on_admin_print_scripts();
		if(is_array($scripts))
		foreach($scripts as $name => $file){
			//wp_register_script($name,$file);
			//add_action('admin_print_scripts',$name);
				wp_enqueue_script($name,$file);
		}
	}
	function print_scripts(){
		$this->on_wp_print_scripts();		
	}
	function get_version_info(){
		global $wp_version;
		
		$body=array('id' => $this->SLUG);
		if($this->UPDATE_SITE_EXTRA)
			$body=$body+$this->UPDATE_SITE_EXTRA;
		
		$options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
		$options['headers']= array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
			'Content-Length' => strlen(implode(',',$body)),		
			'user-agent' => 'WordPress/' . $wp_version,
			'referer'=> get_bloginfo('url')
		);
		$raw_response = wp_remote_post($this->UPDATE_SITE, $options);
		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
			return unserialize($raw_response['body']);
		return array();
	}
	function check_for_update(){
		if(empty($this->UPDATE_SITE) || !is_admin())
			return;

		$checked_data = get_transient('update_plugins');		
		global $wp_version;
		$plugin=$this->pluginname;
		
		$version_info = $this->get_version_info();
		
        if(!$version_info["has_access"] || version_compare($this->VERSION, $version_info["version"], '>=')){
            unset($checked_data->response[$plugin]);
            return;
        }else{
        	$package=$version_info['url'];
        	if($this->UPDATE_SITE_EXTRA)
	        	foreach($this->UPDATE_SITE_EXTRA as $key => $value)
		        	$package=str_replace('{'.$key.'}',urlencode($value),$package);
			$update_data = new stdClass();
			$update_data->slug = $this->app;
			$update_data->new_version = $version_info['version'];
			$update_data->url = $version_info['site'];
			$update_data->package = $package;
			$checked_data->response[$plugin] = $update_data;
		}
		set_transient('update_plugins', $checked_data);
	}
	function version_information(){
		global $wp_version;
		if(!$this->VERSION_INFO_LINK)
			return;
		$body=array('id' => $this->SLUG);
		
		$options = array('method' => 'GET', 'timeout' => 3, 'body' => $body);
		$options['headers']= array(
			'Content-Length' => strlen(implode(',',$body)),		
			'user-agent' => 'WordPress/' . $wp_version,
			'referer'=> get_bloginfo('url')
		);			
		$response=wp_remote_get($this->VERSON_INFO_LINK,$options);
		echo $response;
		exit;
	}
}
?>