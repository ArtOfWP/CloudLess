<?php
abstract class ApplicationBase{
	protected $installfrompath;
	private $models=array();
	public $pluginname;
	public $dir;
	public $app;
	public $options;
	function ApplicationBase($appName,$appDir,$useOptions=false){
		$this->dir=$appDir;
		$this->app=$appName;
		$this->pluginname="$appName/$appName.php";
		$this->installfrompath=$appDir.'/app/core/domain/';
		if(method_exists($this,'on_register_query_vars'))
			add_filter('query_vars', array(&$this,'register_query_vars'));
		if(is_admin()){
			if($useOptions)
				add_action( 'admin_init', array(&$this,'register_settings' ));			
			register_activation_hook($this->pluginname, array(&$this,'activate'));
			register_deactivation_hook($this->pluginname, array(&$this,'deactivate'));
			register_uninstall_hook($this->pluginname, array(&$this,'delete'));
			if(method_exists($this,'on_plugin_page_link'))
				add_filter( 'plugin_action_links_'.$this->pluginname, array(&$this,'plugin_page_links'), 10, 2 );
			if(method_exists($this,'on_after_plugin_row'))
				add_action( 'after_plugin_row_'.$this->pluginname, array(&$this,'after_plugin_row'), 10, 2 );				
			if(method_exists($this,'on_init_admin'))
				add_action('init', array(&$this,'on_init_admin'));				
			if(method_exists($this,'on_admin_menu'))
				add_action('admin_menu',array(&$this,'on_admin_menu'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));	
			if(method_exists($this,'on_admin_print_styles'))
				add_action('admin_init',array(&$this,'print_admin_styles'));							
		}else{
			if(method_exists($this,'on_wp_print_styles'))
				add_action('wp_print_styles',array(&$this,'print_styles'));
			if(method_exists($this,'on_add_page_links'))
				add_filter('wp_list_pages', array(&$this,'on_add_page_links'));	
			if(method_exists($this,'render_view_template'))
				add_filter('render_from_template',array(&$this,'render_view_template'));
			if(method_exists($this,'on_init'))
				add_action('init', array(&$this,'on_init'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));
		}
		if($useOptions){
			$this->options= new WpOption($appName);
			if(method_exists($this,'on_load_options'))
				$this->on_load_options();
		}
	}
	function register_query_vars($public_query_vars){
		$vars=$this->on_register_query_vars();
		return $vars+$public_query_vars;
	}
	function register_settings(){
		WpHelper::registerSettings($this->app,array($this->app));
	}
	function after_plugin_row($plugin_file, $plugin_data){
//		 $plugin_file, $plugin_data, $context
/*
array(9) { ["Name"]=>  string(17) "Wp Affiliate Shop" ["Title"]=>  string(17) "Wp Affiliate Shop" ["PluginURI"]=>  string(26) "http://wpaffiliateshop.com" ["Description"]=>  string(59) "Makes it easy to integrate an affiliate shop into WordPress" ["Author"]=>  string(15) "Cyonite Systems" ["AuthorURI"]=>  string(26) "http://cyonitesystems.com/" ["Version"]=>  string(4) "9.09" ["TextDomain"]=>  string(0) "" ["DomainPath"]=>  string(0) "" } 
 */
	echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">There is a new version of '.$plugin_data['Name'].' available. <a href="http://andreasnurbo.com/thesite/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=all-in-one-seo-pack&amp;TB_iframe=true&amp;width=640&amp;height=754" class="thickbox" title="All in One SEO Pack">View version 1.6.7 Details</a> or <a href="update.php?action=upgrade-plugin&amp;plugin=all-in-one-seo-pack%2Fall_in_one_seo_pack.php&amp;_wpnonce=f30aed80d5">upgrade automatically</a>.</div></td></tr>';
//    echo '<tr class="plugin-update-tr"><td colspan="5" class="plugin-update">' . $plugin_file .' '. $context . '</td></tr>';
	}
	function plugin_page_links($links){
//		static $this_plugin;
//		if ( ! $this_plugin ) $this_plugin = "$this->app/$this->app.php";
		
//		if ( $file == $this_plugin ){
			$plugin_link=$this->on_plugin_page_link();
			array_unshift( $links, $plugin_link); // before other links
//		}
		return $links;
	}
	function activate(){
		if(method_exists($this,'onactivate'))
			$this->onactivate();
		AoiSoraSettings::addApplication($this->app,$this->dir);
	}
	function deactivate(){
		if(method_exists($this,'ondeactivate'))
			$this->ondeactivate();
		AoiSoraSettings::removeApplication($this->app);
	}
	function installed(){
		return AoiSoraSettings::installed($this->app);
	}
	public function install(){
		if(method_exists($this,'onpreinstall'))
			$this->onpreinstall();		
		Debug::Value('Install from path',$this->installfrompath);
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;
		$this->create();
		if($result)
			AoiSoraSettings::installApplication($this->app);			
		if(method_exists($this,'onafterinstall'))
			$this->onafterinstall();			
		return $result;
	}
	private function create(){
		global $db;
		foreach($this->models as $model){
			$m = new $model();
			$db->createTable($m);
		}
		$db->createStoredRelations();
	}
	public function uninstall(){
		if(method_exists($this,'onpreuninstall'))
			$this->onpreuninstall();
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;		
		$this->drop();
		if($result)
			AoiSoraSettings::uninstallApplication($this->app);
		if(method_exists($this,'onafteruninstall'))
			$this->onafteruninstall();			
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
}
?>