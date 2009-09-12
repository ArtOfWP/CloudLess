<?php
class CountQuery{
	function CountQuery($table=false){
		if($table){
			$this->from($table);	
		}
	}
	static function createFrom($object,$lazy=true){
		$maintable=strtolower(get_class($object));
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
		$this->statement['from'][]=$this->addMark($table);
		return $this;		
	}
	public function select($property,$table=false){
		$property=$this->addMark(strtolower($property));
		$this->statement['select'][]=$table?'COUNT('.$this->addMark($table).'.'.$property.')':'COUNT('.$property.')';
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
	public function hasLimit(){
		return !empty($this->limit);
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
		$rows=$db->query($this);
		$count=0;
		if(sizeof($rows)>0)
			$count=(int)$rows[0]['total'];
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
				return $this->statement[$property];
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}
}
?>