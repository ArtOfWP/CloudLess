<?php
class CountQuery{
	function CountQuery($table=false){
		if($table){
			$this->from($table);	
		}
	}
	static function createFrom($class,$lazy=true){
		if(is_object($class))
			$class=get_class($class);
		$maintable=strtolower($class);
		$q = new CountQuery($maintable);
		return $q;
	}
	static function create(){
		return new CountQuery();
	}
	private $statement=array();
	var $depends=array();
	var $returnType;
	public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark($db_prefix.$table);
		return $this;
	}
	public function selectDistinct($property,$table=false){
		global $db_prefix;
		$property=$this->addMark(strtolower($property));
		$this->statement['select'][]=$table?'COUNT( DISTINCT '.$this->addMark($db_prefix.$table).'.'.$property.')':'COUNT( DISTINCT '.$property.')';
		return $this;
	}
	public function select($property,$table=false){
		global $db_prefix;
		$property=$this->addMark(strtolower($property));
		$this->statement['select'][]=$table?'COUNT('.$this->addMark($db_prefix.$table).'.'.$property.')':'COUNT('.$property.')';
		return $this;
	}
	public function where($restriction){
		if(is_array($restriction)){
			$this->statement['where']=((array)$this->statement['where'])+$restriction;
		}else
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
		$this->limit=1;
		$this->offset=0;
		$rows=$db->query($this);
		$count=0;
		if(sizeof($rows)>0)
			$count=(int)array_pop($rows[0]);//['total'];
		return $count;
	}
	
	function __get($property){
		switch($property){
			case 'select':
				if(sizeof($this->statement['select'])===0)
					$this->statement['select'][]='COUNT(*) as total';
				return $this->statement['select'];
			case 'from':	
			case 'where':
			case 'groupby':
				return $this->statement[$property];
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}
}