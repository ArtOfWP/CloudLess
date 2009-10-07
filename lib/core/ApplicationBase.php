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
		}else{
			if(method_exists($this,'on_add_page_links'))
				add_filter('wp_list_pages', array(&$this,'on_add_page_links'));	
			if(method_exists($this,'render_view_template'))
				add_filter('render_from_template',array(&$this,'render_view_template'));
			if(method_exists($this,'on_init'))
				add_action('init', array(&$this,'on_init'));
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
    echo '<tr class="plugin-update-tr"><td colspan="5" class="plugin-update">' . $plugin_file .' '. var_dump($plugin_data) .' '. $context . '</td></tr>';
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
}
?>