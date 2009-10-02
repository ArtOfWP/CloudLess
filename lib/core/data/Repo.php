<?php
class Repo{
	static function findAll($class){
		return Query::createFrom($class)->execute();		
	}
	static function getById($class,$id,$lazy=false){
		$objects= Query::createFrom($class,$lazy)
				  ->where(R::Eq(new $class,$id))
				  ->execute();
		return sizeof($objects)==1?$objects[0]:false;
	}
	static function findByProperty($class,$property,$value,$lazy=false){
		return Query::createFrom($class,$lazy)->where(R::Eq($property,$value))->execute();		
	}
	static function slicedFindAll($class,$firstResult,$maxResult,$order,$restrictions){
		return Query::createFrom($class)
			->limit($firstResult,$maxResult)
			->orderBy($order)
			->where($restrictions)
			->execute();		
	}
	static function total($class){
		return CountQuery::createFrom($class)->execute();
	}
}
?>