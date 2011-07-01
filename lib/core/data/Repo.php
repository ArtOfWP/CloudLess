<?php
class Repo{
	static function findAll($class,$lazy=false,$order=false){
		if($order)
			return Query::createFrom($class,$lazy)->order($order)->execute();
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
	static function find($class,$lazy=false,$restrictions=false,$groupby=false,$order=false){
		if($groupby || $restrictions){
			$q=Query::createFrom($class,$lazy);
			if($groupby)
				$q->groupBy($groupby);
			if($restrictions)
				$q->where($restrictions);
			if($order)
				$q->order($order);
			return $q->execute();
		}
		else
			return self::findAll($class,$lazy,$order);
	}
	static function findByProperty($class,$property,$value,$lazy=false,$order=false){
		$q=Query::createFrom($class,$lazy);
		if(is_array($value))
			$q->where(R::In($property,$value));		
		else 
			$q->where(R::Eq($property,$value));
		if($order)
			$q->order($order);
		return $q->execute();
	}
	static function slicedFindAll($class,$firstResult,$maxResult,$order=false,$restrictions=false,$groupby=false){
		$query=Query::createFrom($class,true)->limit($firstResult,$maxResult);
		if($order)
			$query->order($order);
		if($restrictions)
			$query->where($restrictions);
		if($groupby)
			if(is_array($groupby))
				foreach($groupby as $param)
					$query->groupby($param);
			else
				$query->groupby($groupby);
		return $query->execute();
	}
	static function findOne($class,$requirement,$lazy=false){
		$result=array();
		if($requirement instanceof R)
			$result= Query::createFrom($class,$lazy)->where($requirement)->limit(0,1)->execute();
		else
			die('Supplied $requirement parameter is not an R(equirement) object');
		if(sizeof($result)>0)
			return $result[0];
		else
			return false;
	}
	static function total($class,$restrictions=false,$groupby=false){
		$q=CountQuery::createFrom($class);
		if($restrictions)
			$q->where($restrictions);
		if($groupby)
			$q->groupBy($groupby);
		return $q->execute();
	}
}