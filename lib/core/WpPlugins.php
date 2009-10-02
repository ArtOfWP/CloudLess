<?php
class WpPlugins{
	static function is_plugin_active($pluginname){
		$plugins=get_option('active_plugins');
		foreach($plugins as $plugin){
			if(stristr($plugin,$pluginname))
				return true;
		}
		return false;
	}
}
?>