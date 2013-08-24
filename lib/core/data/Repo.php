<?php
/**
 * Class Repo
 */
class Repo{
    /**
     * Finds all entries of type $class
     * @param string|object $class name of class or instance of object
     * @param bool $lazy if objects should be preloaded
     * @param bool $order sorting order
     * @return array
     */
    static function findAll($class, $lazy=false, $order=false){
		Debug::Value('Repo findAll ', $class);
		Debug::Backtrace();
		
		if($order)
			return Query::createFrom($class,$lazy)->order($order)->execute();
		return Query::createFrom($class,$lazy)->execute();
	}

    /**
     * Get an entry by its Id
     * @param string|object $class name of class or instance of object
     * @param int $id the id of the entry
     * @param bool $lazy if object should be preloaded
     * @return bool|mixed
     */
    static function getById($class, $id, $lazy=false){
		Debug::Value('Repo::getById',$class);
		Debug::Value('Id=',$id);
		Debug::Backtrace();		
		$objects= Query::createFrom($class,$lazy)
				  ->where(R::Eq(new $class,$id))
				  ->limit(0,1)
				  ->execute();
		return sizeof($objects) == 1 ? array_shift($objects) : false;
	}

    /**
     * Finds all entries of type $class that matches the restrictions
     * @param string|object $class name of class or instance of object
     * @param bool $lazy if object should be preloaded
     * @param R|R[] $restrictions list of restrictions to limit the find
     * @param string $groupby group by property
     * @param Order|Order[] $order
     * @return array
     */
    static function find($class, $lazy=false, $restrictions=null, $groupby=null, $order=null){
		Debug::Value('Repo find ', $class);
		Debug::Backtrace();		

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

    /**
     * Finds all entries of type $class that matches the property with value
     * @param string|object $class name of class or instance of object
     * @param string $property property to match against
     * @param mixed $value value of property to match against
     * @param bool $lazy if object should be preloaded
     * @param Order|Order[] $order order the result
     * @return array
     */
    static function findByProperty($class, $property, $value, $lazy=false, $order=null){
		Debug::Value('Repo findByProperty ',$class);
		Debug::Value('Repo findByProperty ->',$property);
		Debug::Backtrace();
		$q=Query::createFrom($class,$lazy);
		if(is_array($value))
			$q->where(R::In($property,$value));		
		else 
			$q->where(R::Eq($property,$value));
		if($order)
			$q->order($order);
		return $q->execute();
	}
    /**
     * Find a offset and limited list of entries
     * @param string|object $class name of class or instance of object
     * @param int $firstResult from which entry that return should begin
     * @param int $maxResult limit the number of entries
     * @param Order|Order[] $order order the result
     * @param R|R[] $restrictions list of restrictions to limit the find
     * @param string|string[] $groupby group by property
     * @param bool $lazy if object should be preloaded
     * @return array
     */
	static function slicedFindAll($class, $firstResult, $maxResult, $order=nulk, $restrictions=null, $groupby=null,$lazy=false){
		Debug::Value('Repo slicedFindAll ', $class);
		Debug::Backtrace();
		$q=Query::createFrom($class,$lazy);
		$q->limit($firstResult,$maxResult);
		if($order)
			$q->order($order);
		if($restrictions)
			$q->where($restrictions);
		if($groupby)
			if(is_array($groupby))
				foreach($groupby as $param)
					$q->groupby($param);
			else
				$q->groupby($groupby);
		return $q->execute();
	}

    /**
     * Find one entry matching
     * @param $class
     * @param R|R[] $restrictions list of restrictions to limit the find
     * @param bool $lazy if object should be preloaded
     * @return null|mixed
     */
    static function findOne($class, $restrictions,$lazy=false){
		Debug::Value('Repo findOne', $class);
		Debug::Backtrace();		
		$result=array();
		$result= Query::createFrom($class,$lazy)->where($restrictions)->limit(0,1)->execute();
		if(sizeof($result)>0)
			return array_shift($result);
		else
			return null;
	}

    /**
     * Get total number of entries matching $restrictions
     * @param $class
     * @param bool $restrictions list of restrictions to limit the total
     * @param string|string[] $groupby group by property
     * @return int number of entries matching restrictions
     */
    static function total($class, $restrictions=null, $groupby=null){
		Debug::Value('Repo total ', $class);
		Debug::Backtrace();
		
		$q=CountQuery::createFrom($class);
		if($restrictions)
			$q->where($restrictions);
		if($groupby)
			$q->groupBy($groupby);
		return $q->execute();
	}
}