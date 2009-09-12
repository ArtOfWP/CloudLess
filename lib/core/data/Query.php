<?php
class Delete{
	function Delete($table=false){
		if($table)
			$this->from($this->addMark($table));
	}
	static function createFrom($object,$lazy=true){
		$maintable=strtolower(get_class($object));
		$d = new Delete($maintable);
		return $d;
	}
	static function create(){
		return new Delete();
	}	
	private $statement=array();
	public function from($table){
		$this->statement['from'][]=$this->addMark($table);
		return $this;		
	}
	public function where($restriction){
		if($restriction)
			$this->statement['where'][]=$restriction;
		return $this;
	}
	public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}
	public function hasWhere(){
		return !empty($this->statement['where']);
	}
	public function getStatement(){
		return $this->statement;
	}
	public function setParameter($param,$value){
		foreach($this->statement['where'] as $restriction){
			$restriction->setParameter($param,$value);
		}
	}
	function execute(){
		global $db;
		return $db->delete($this);
	}
	
	function __get($property){
		switch($property){
			case 'from':
			case 'where':
				return $this->statement[$property];
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}
}

class Query{
	function Query($table=false){
		if($table)
			$this->from($this->addMark($table));
	}
	static function createFrom($object,$lazy=true){
		$maintable=strtolower(get_class($object));
		$q = new Query($maintable);
		$q->returnType=get_class($object);
		$properties =ObjectUtility::getProperties($object);
		foreach($properties as $property){
			if(!$lazy){
				$dependson=array_key_exists_v('dbrelation',ObjectUtility::getCommentDecoration($object,'get'.$property));
				if($dependson){
					'select * from this, dependson where this.property=dependson.id';
					$temp= new $dependson();
					$table=strtolower($dependson);
					$gProperty= Query::createFrom($temp,$lazy)
								->where(R::Eq($temp,'id'));
					$q->depends[$property]=$gProperty;
				}
			}
			$q->select($property,$maintable);
		}
		return $q;
	}
	static function create(){
		return new Query();
	}
	private $statement=array();
	var $depends=array();
	var $returnType;
	public function from($table){
		$this->statement['from'][]=$this->addMark($table);
		return $this;		
	}
	public function select($property,$table=false){
		$property=$this->addMark(strtolower($property));
		$this->statement['select'][]=$table?$this->addMark($table).'.'.$property:$property;
		return $this;		
	}
	public function where($restriction){
		if($restriction)
			$this->statement['where'][]=$restriction;
		return $this;
	}
	public function limit($offset,$limit){
		$this->limit=$limit;
		$this->offset=$offset;
		return $this;
	}
	public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}
	public function order($order){
		if(is_array($order))
			$this->statement['order']+=$order;
		else
			$this->statement['order'][]=$order;
	}
	public function hasWhere(){
		return !empty($this->statement['where']);
	}
	public function hasLimit(){
		return !empty($this->limit);
	}
	public function getStatement(){
		return $this->statement;
	}
	public function getValues(){
		
	}
	public function setParameter($param,$value){
		foreach($this->statement['where'] as $restriction){
			$restriction->setParameter($param,$value);
		}
	}
	function execute(){
		global $db;
		$rows=$db->query($this);
		$class=$this->returnType;
		$objects=array();
		if(sizeof($rows)>0)
			foreach($rows as $row){
				$object = new $class();			
				ObjectUtility::setProperties($object,$row);
				if(sizeof($this->depends)>0){
					echo 'Has dependings';
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
		return $objects;
	}
	
	function __get($property){
		switch($property){
			case 'from':
			case 'select':
			case 'where':
				return $this->statement[$property];
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}
}
?>