<?php
class Repo{
	static function findAll($class,$lazy=false){
		return Query::createFrom($class,$lazy)->execute();		
	}
	static function getById($class,$id,$lazy=false){
		Debug::Value('Repo::getById',$class);
		Debug::Value('Id=',$id);
		$objects= Query::createFrom($class,$lazy)
				  ->where(R::Eq(new $class,$id))
				  ->limit(0,1)
				  ->execute();
		return sizeof($objects)==1?$objects[0]:false;
	}
	static function findByProperty($class,$property,$value,$lazy=false){
		if(is_array($value))
			return Query::createFrom($class,$lazy)->where(R::In($property,$value))->execute();		
		return Query::createFrom($class,$lazy)->where(R::Eq($property,$value))->execute();		
	}
	static function slicedFindAll($class,$firstResult,$maxResult,$order,$restrictions){
		return Query::createFrom($class)
			->limit($firstResult,$maxResult)
			->order($order)
			->where($restrictions)
			->execute();		
	}
	static function total($class){
		return CountQuery::createFrom($class)->execute();
	}
}
?>