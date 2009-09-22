<?php
class AoiSoraSettings{
	static function addApplication($name,$loadpath){
		global $aoiSoraApp;
		$apps=$aoiSoraApp->options->applications;
		if(!is_array($apps))
			$apps=array();
		$apps[$name]=$loadpath;
		$aoiSoraApp->options->applications=$apps;
//		$aoiSoraApp->options->applications[$name]=$loadpath;
		$aoiSoraApp->options->save();
		Debug::Message('AddApplication');
		Debug::Value('Options',$aoiSoraApp->options->getArray());
	}
	static function removeApplication($name){
		global $aoiSoraApp;
		$apps=$aoiSoraApp->options->applications;
		unset($apps[$name]);
		$aoiSoraApp->options->applications=$apps;
		$aoiSoraApp->options->save();
		Debug::Message('RemoveApplication');
		Debug::Value('Options',$aoiSoraApp->options->getArray());		
	}
	static function getApplications(){
		global $aoiSoraApp;
		return $aoiSoraApp->options->applications;
	}
	static function installApplication($app){
		global $aoiSoraApp;
		$inst=	$aoiSoraApp->options->installed;
		if(!is_array($inst))
			$inst=array();
		$inst[]=$app;
		$aoiSoraApp->options->installed=$inst;
//		$aoiSoraApp->options->installed[]=$app;
		$aoiSoraApp->options->save();
	}
	static function uninstallApplication($app){
		global $aoiSoraApp;
		$apps=$aoiSoraApp->options->installed;
		$apps = array_diff($apps,array($app));
		$apps=array_values($apps);
		$aoiSoraApp->options->installed=$apps;
		$aoiSoraApp->options->save();		
	}	
	static function installed($app){
		global $aoiSoraApp;
		$apps=$aoiSoraApp->options->installed;
		if($apps)
			if(in_array($app,$apps))
				return true;
		return false;
	}
}
?>