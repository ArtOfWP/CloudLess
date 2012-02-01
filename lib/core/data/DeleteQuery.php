<?php
class Delete{
	function Delete($table=false){
		$this->statement['from']=array();	
		$this->statement['where']=array();			
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
	static function create($table=false){
		return new Delete($table);
	}	
	private $statement=array();
	public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark($db_prefix.$table);
		return $this;		
	}
	public function where($restriction){
        if(is_array($restriction)){
            $this->statement['where']=array_merge(((array)$this->statement['where']),$restriction);
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
		return isset($this->statement['where']) && !empty($this->statement['where']) && sizeof($this->statement['where']) && $this->statement['where'][0]!=null;
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
				if(isset($this->statement[$property]))
					return $this->statement[$property];
				return array();
		}
	}
	private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.strtolower($ct).'`';
	}
}