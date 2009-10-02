<?php
class ObjectUtility{

	static function getProperties($object){
		$class = new ReflectionClass(get_class($object));
		$methods= $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$properties=array();
		foreach($methods as $method)
			if(strpos($method->getName() ,'get')!==false && !$method->isStatic()){
				$property=str_replace('get','',$method->getName());
				$properties[]=$property;	
			}
		return $properties;
	}
	
	static function getArrayProperties($object){
		$class = new ReflectionClass(get_class($object));
		$methods= $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$properties=array();
		foreach($methods as $method)
			if(strpos($method->getName() ,'List')!==false && !$method->isStatic()){
				$property=str_replace('List','',$method->getName());
				$properties[]=$property;	
			}
		return $properties;
	}
	
	static function getArrayPropertiesAndValues($object){
		$class = new ReflectionClass(get_class($object));
		$methods= $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$properties=array();
		foreach($methods as $method)
			if(strpos($method->getName(),'List')!==false){
				$properties[str_replace('List','',$method->getName())]=$method->invoke($object);	
			}
		return $properties;
	}
	static function addToArray($object,$method,$values){
		Debug::Message('AddToArrayMethod');
		Debug::Value($method,$values);
		$class = new ReflectionClass(get_class($object));
		$method=new ReflectionMethod(get_class($object),'add'.$method);
		foreach($values as $value){
			$method->invoke($object,$value);
		}
	}
	
	static function getPropertiesAndValues($object){
		$class = new ReflectionClass(get_class($object));
		$methods= $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$properties=array();
		foreach($methods as $method){
			if(strpos($method->getName(),'get')!==false && !$method->isStatic()){
				$properties[str_replace('get','',$method->getName())]=$method->invoke($object);
			}
		}
		return $properties;
	}
	
	static function setProperties($object,$values){
		foreach($values as $property => $value){
			$method=new ReflectionMethod(get_class($object),'set'.$property);
			$method->invoke($object,$value);
		}	
	}
	
	static function getCommentDecoration($object,$method){
		$rmethod=new ReflectionMethod(get_class($object),$method);
		$comment=$rmethod->getDocComment();
		$comment=str_replace('/**','',$comment);
		$comment=str_replace('*/','',$comment);
		$settings=array();
		if(strlen($comment)>4){
			$temp=explode(',',$comment);
			foreach($temp as $setting){
				$x=explode(':',trim($setting));
				$settings[$x[0]]=$x[1];
			}
		}
		return $settings;
	}
}
?>