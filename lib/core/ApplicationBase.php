<?php
abstract class ApplicationBase{
	protected $installfrompath;
	private $models=array();
	public $pluginname;
	public $dir;
	public $app;
	public $options;
	private $useInstall;
	private $useOptions;
	function ApplicationBase($appName,$appDir,$useOptions=false,$useInstall=false){
		$this->dir=$appDir;
		$this->app=$appName;
		$this->pluginname="$appName/$appName.php";
		$this->installfrompath=$appDir.'/app/core/domain/';
		$this->useInstall=$useInstall;
		$this->useOptions=$useOptions;
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
			if(method_exists($this,'on_plugin_row_message'))
				add_action( 'after_plugin_row_'.$this->pluginname, array(&$this,'after_plugin_row'), 10, 2 );				
			if(method_exists($this,'on_init_admin'))
				add_action('init', array(&$this,'on_init_admin'));				
			if(method_exists($this,'on_admin_menu'))
				add_action('admin_menu',array(&$this,'on_admin_menu'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));	
		}else{
			if(method_exists($this,'on_wp_print_styles'))
				add_action('wp_print_styles',array(&$this,'print_styles'));
			if(method_exists($this,'on_wp_print_scripts'))
				add_action('wp_print_scripts',array(&$this,'print_scripts'));
			if(method_exists($this,'on_add_page_links'))
				add_filter('wp_list_pages', array(&$this,'on_add_page_links'));	
			if(method_exists($this,'render_view_template'))
				add_filter('render_from_template',array(&$this,'render_view_template'));
			if(method_exists($this,'on_init'))
				add_action('init', array(&$this,'on_init'));
			if(method_exists($this,'on_rewrite_rules_array'))
				add_filter('rewrite_rules_array',array(&$this,'on_rewrite_rules_array'));
			if(method_exists($this,'on_render_footer'))
				add_action('wp_footer',array(&$this,'on_render_footer'));
		}
		if($useOptions){			
			$this->options= new WpOption($this->app);
			if(method_exists($this,'on_load_options'))
				$this->on_load_options();			
		}
		Debug::Value($appName,$this->app);		
	}
	function register_query_vars($public_query_vars){
		$vars=$this->on_register_query_vars();
		return $vars+$public_query_vars;
	}
	function register_settings(){
		WpHelper::registerSettings($this->app,array($this->app));
	}
	function after_plugin_row($plugin_file, $plugin_data){
/*
array(9) { ["Name"]=>  ["Title"]=> "Wp Affiliate Shop" ["PluginURI"]=>  ["Description"]=>   ["Author"]=>  ["AuthorURI"]=> ["Version"]=> ["TextDomain"]=>  ["DomainPath"]=>   } 
 */
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
		if(!$this->useInstall)
			AoiSoraSettings::installApplication($this->app);
		if($this->useOptions){			
			$this->options= new WpOption($this->app);
			if(method_exists($this,'on_load_options'))
				$this->on_load_options();		
		}			
		AoiSoraSettings::addApplication($this->app,$this->dir);
		if(method_exists($this,'on_activate'))
			$this->on_activate();		
	}
	function deactivate(){
		if(method_exists($this,'on_deactivate'))
			$this->on_deactivate();
		if(!$this->useInstall)
			AoiSoraSettings::uninstallApplication($this->app);
		if($this->useOptions){			
			$this->options= new WpOption($this->app);
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
		foreach($scripts as $name => $file){
			//wp_register_script($name,$file);
			//add_action('admin_print_scripts',$name);
				wp_enqueue_script($name,$file);
		}
	}
	function print_scripts(){
		$this->on_wp_print_scripts();		
	}
}
?>