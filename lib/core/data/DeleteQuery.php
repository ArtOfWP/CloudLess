<?php
class Delete{
	function Delete($table=false){
		if($table){
			$this->from($table);
		}
	}
	static function createFrom($class,$lazy=true){
		if(is_object($class))
			$class=get_class($class);
		$maintable=strtolower($class);
		$d = new Delete($maintable);
		return $d;
	}
	static function create(){
		return new Delete();
	}	
	private $statement=array();
	public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark($db_prefix.$table);
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
?>