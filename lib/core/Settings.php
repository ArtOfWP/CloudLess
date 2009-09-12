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
	static function getApplications(){
		$s = new Setting();
		return Query::createFrom($s)->where(R::Eq('Key','path'))->execute();
	}
}
?>