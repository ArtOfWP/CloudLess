<?php
class AoiSoraSettings{
	static function addApplication($name,$loadpath,$version){
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		if(!is_array($apps))
			$apps=array();
		$apps[$name]['path']=$loadpath;
		$apps[$name]['version']=$version;
		$aoiSoraApp->options->updateValue('applications',$apps);
		$aoiSoraApp->options->save();
		Debug::Message('AddApplication');
//		Debug::Value('Options',$ops->getArray());
	}
	static function removeApplication($name){
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		unset($apps[$name]);
		$aoiSoraApp->options->updateValue('applications',$apps);
		$aoiSoraApp->options->save();
		Debug::Message('RemoveApplication');
		Debug::Value('Options',$aoiSoraApp->options->getArray());		
	}
	static function getApplicationVersion($name){
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		if(isset($apps[$name]['version']))
			return $apps[$name]['version'];
		return false;
	}
	static function getApplications(){
		$aoiSoraApp = AoiSora::instance();
		return $aoiSoraApp->options->applications;
	}
	static function installApplication($app){
		Debug::Message('Install application');
		$aoiSoraApp = AoiSora::instance();
		$inst=	$aoiSoraApp->options->installed;
		if(!is_array($inst))
			$inst=array();
		$inst[]=$app;
		$aoiSoraApp->options->updateValue('installed',$inst);
		$aoiSoraApp->options->save();
	}
	static function uninstallApplication($app){
		Debug::Message('Uninstall application');		
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->installed;
		$apps = array_diff($apps,array($app));
		$apps=array_values($apps);
		$aoiSoraApp->options->updateValue('installed',$apps);
		$aoiSoraApp->options->save();		
	}	
	static function installed($app){
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->installed;
		return in_array($app,(array)$apps);
	}
}