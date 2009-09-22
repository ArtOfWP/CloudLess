<?php
class WpPlugins{
	function is_plugin_active($pluginname){
		$plugins=get_option('active_plugins');
		foreach($plugins as $plugin){
			if(stristr($plugin,$pluginname))
				return true;
		}
		return false;
	}
}
?>