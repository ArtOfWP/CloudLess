<?php
class AoiSoraSettings{
	static function addApplication($name,$loadpath){
		$setting = new Setting();
		$setting->setApplication($name);
		$setting->setKey('path');
		$setting->setValue($loadpath);
		$setting->save();
//		Debug::Message('AddApplication');
//		Debug::Value('Setting',$setting->getValue());
	}
	static function removeApplication($name){
		$setting = new Setting();
		Delete::createFrom($setting)->whereAnd(R::Eq('application',$name))->where(R::Eq('key','path'))->execute();
	}
	static function getApplications(){
		$s = new Setting();
		return Query::createFrom($s)->where(R::Eq('Key','path'))->execute();
	}
	static function installApplication($app){
		$setting = new Setting();
		$setting->setApplication($app);
		$setting->setKey('installed');
		$setting->setValue(true);
		$setting->save();
	}
	static function uninstallApplication($app){
		$setting = new Setting();
		Delete::createFrom($setting)->whereAnd(R::Eq('application',$app))->where(R::Eq('key','installed'))->execute();
	}	
	static function installed($app){
		$s = new Setting();
		$r=Query::createFrom($s)->whereAnd(R::Eq('application',$app))->where(R::Eq('key','installed'))->execute();
		if(isset($r) && !empty($r) && sizeof($r)>0)
			return true;
		return false;
	}
}
?>