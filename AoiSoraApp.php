<?php
class AoiSoraApp{
	public $options;
	protected $VERSION='10.5.1';
	protected $UPDATE_SITE='http://artofwp.com/?free_update=plugin';
	protected $SLUG='php-mvc-for-wordpress-(aoisora)';	
	protected $pluginname="AoiSora/AoiSora.php";
	protected $app='AoiSora';
	public function AoiSoraApp(){		
		$this->options= Option::create('AoiSora');
		if($this->options->isEmpty()){
			$this->options->applications=array();
			$this->options->installed=array();
			$this->options->save();
		}
		add_action( 'admin_init', array(&$this,'register_settings' ));			
		register_activation_hook($this->pluginname, array(&$this,'activate'));
		register_deactivation_hook($this->pluginname, array(&$this,'deactivate'));
		register_uninstall_hook($this->pluginname, array(&$this,'delete'));
		$this->load_js();
//		if(is_admin())
//			$this->check_for_update();
	}
	function load_js(){
		wp_register_script('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core'));	
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
		wp_register_script('jquery-dialog',includes_url('/js/jquery/ui.dialog.js'));
		wp_register_script('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js");
		if(is_admin()){
			wp_register_script('jquery-ui-tag-it',plugins_url('AoiSora/lib/js/jquery.ui.tag-it/ui.tag-it.js'),array('jquery','jquery-ui-core'));			
			wp_register_style('forms',plugins_url('AoiSora/lib/css/forms.css'));			
			wp_register_style('wordpress',plugins_url('AoiSora/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));			
		}
	}
	function activate(){
		AoiSoraSettings::addApplication($this->app,$this->dir,$this->VERSION);	
	}
	function deactivate(){

	}
	function delete(){
		$this->options= Option::create('AoiSora');
		$this->options->delete();
	}
	function register_settings(){
		WpHelper::registerSettings("AoiSora/AoiSora.php",array('AoiSora'));		
	}
	function get_version_info(){
		global $wp_version;
		
		$body=array('id' => $this->SLUG);
		
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
		$checked_data = get_transient('update_plugins');		
		global $wp_version;
		$plugin=$this->pluginname;
		$version_info = $this->get_version_info();
		
        if(version_compare($this->VERSION, $version_info["version"], '>=')){
            unset($checked_data->response[$plugin]);
            return;
        }else{
			$update_data = new stdClass();
			$update_data->slug = $this->app;
			$update_data->new_version = $version_info['version'];
			$update_data->url = $version_info['site'];
			$update_data->package = $version_info['url'];
			$checked_data->response[$plugin] = $update_data;
		}
		set_transient('update_plugins', $checked_data);
	}	
}
	global $aoiSoraApp;
	$aoiSoraApp=new AoiSoraApp();
?>