<?php

class Query{
	public $limit;
	public $offset;
	function Query($table=false){
		$this->statement['from']=array();
		$this->statement['order']=array();
		$this->statement['groupby']=array();
		$this->statement['select']=array();			
		$this->statement['where']=array();					
		if($table){
			$this->from($table);
		}
	}
	static function createFrom($class,$lazy=false){
		if(is_object($class))
			$class=get_class($class);
		$maintable=strtolower($class);
		$q = new Query($maintable);
		$q->returnType=$class;
		$object = new $class();
		Debug::Value('createFrom',$class);
		$properties =ObjectUtility::getProperties($object);
		foreach($properties as $property){
			if($lazy){
				$dependson=array_key_exists_v('dbrelation',ObjectUtility::getCommentDecoration($object,'get'.$property));
				Debug::Value('dbrelation',$dependson);
				if($dependson){
					//'select * from this, dependson where this.property=dependson.id';
					$temp= new $dependson();
//					$table=$db_prefix.strtolower($dependson);
					$gProperty= Query::createFrom($temp,false)
								->where(R::Eq($temp,'id'));
					Debug::Value('Depends',R::Eq($temp,'id')->toSQL());
					$q->depends[$property]=$gProperty;
				}
			}
			$q->select($property,$maintable);
		}/*
		$arrays=ObjectUtility::getArrayProperties($object);
		foreach($arrays as $array){
			if($lazy){
				$dependson=array_key_exists_v('dbrelation',ObjectUtility::getCommentDecoration($object,$array));
				Debug::Value('dbrelation',$dependson);
				
				if($dependson){
					$dbrelationname=array_key_exists_v('dbrelationname',ObjectUtility::getCommentDecoration($object,$array));
					//'select * from this, dependson where this.property=dependson.id';
					$temp= new $dependson();
//					$table=$db_prefix.strtolower($dependson);
					$gProperty= Query::createFrom($temp,false)
								->where(R::Eq($temp,'id'));
					Debug::Value('Depends',R::Eq($temp,'id')->toSQL());
					$q->depends[$array]=$gProperty;
				}
			}
		}*/		
		return $q;
	}
	static function create($table=false){
		return new Query($table);
	}
	private $statement=array();
	var $depends=array();
	var $returnType;
	public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark(strtolower($db_prefix.$table));
		return $this;
	}
	public function selectDistinct($property,$table=false){
		global $db_prefix;
		if($property instanceof SelectFunction){
			$this->statement['select'][]='DISTINCT '.$property->toSQL($this->addMark($property->getColumn()));
		}else{
			$property=$this->addMark(strtolower($property));
			$this->statement['select'][]=$table?'DISTINCT '.$this->addMark($db_prefix.$table).'.'.$property:'DISTTINCT '.$property;
		}
		return $this;
	}
	
	public function select($property,$table=false){
		global $db_prefix;
		if($property instanceof SelectFunction){
			$this->statement['select'][]=$property->toSQL($this->addMark($property->getColumn()));
		}else{
			$property=$this->addMark(strtolower($property));
			$this->statement['select'][]=$table?$this->addMark($db_prefix.$table).'.'.$property:$property;
		}
		return $this;		
	}
	public function where($restriction){
		if(is_array($restriction)){
			$this->statement['where']=((array)$this->statement['where'])+$restriction;
		}else
			$this->statement['where'][]=$restriction;
		return $this;
	}
	public function limit($offset,$limit){
		$this->limit=$limit;
		$this->offset=$offset;
		return $this;
	}
	public function And_($restriction){
		$this->statement['where'][]=R::_And();
		$this->where($restriction);
		return $this;
	}
	public function Or_($restriction){
		$this->statement['where'][]=R::_Or();
		$this->where($restriction);
		return $this;
	}	
	public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}
	public function whereOr($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_Or();
		return $this;
	}	
	public function order($order){
		if(is_array($order))
			$this->statement['order']=((array)$this->statement['order'])+$order;
		else
			$this->statement['order'][]=$order;
		return $this;
	}
	public function hasWhere(){
		return isset($this->statement['where']) && !empty($this->statement['where']) && sizeof($this->statement['where']) && $this->statement['where'][0]!=null;
	}
	public function hasLimit(){
		return !empty($this->limit);
	}
	public function getStatement(){
		return $this->statement;
	}
	public function groupBy($property){
		$this->statement['groupby'][]=$this->addMark(strtolower($property));
		return $this;
	}
	public function setParameter($param,$value){
		foreach($this->statement['where'] as $restriction){
			$restriction->setParameter($param,$value);
		}
	}
	function execute(){
		global $db;
		$rows=$db->query($this);
		Debug::Value('Rows returned',sizeof($rows));
		if(isset($this->returnType)){
			$class=$this->returnType;
			$objects=array();
			if(sizeof($rows)>0){
				foreach($rows as $row){
					$object = new $class();			
					ObjectUtility::setProperties($object,$row);
					if(sizeof($this->depends)>0){
						foreach($this->depends as $property => $query){
							$getproperty='get'.$property;
							$value=(int)$object->$getproperty();
							if($value){			
								$query->setParameter('id',$value);
								$value=$query->execute();
								ObjectUtility::setProperties($object,array($property => $value[0]));
							}
						}
					}
					$objects[]=$object;
				}
			}
			return $objects;
		}else
			return $rows;
	}
	
	function __get($property){
		switch($property){
			case 'from':
			case 'select':
			case 'where':
			case 'order':
			case 'groupby':
				if(isset($this->statement[$property]))
					return $this->statement[$property];
				return array();
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}
}