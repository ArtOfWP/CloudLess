<?php
class R{
	var $table;
	var $column;
	var $foreigntable;
	var $foreigncolumn;
	var $value;
	var $method;
		
	static function Eq($property,$value,$isProperty=false){
		global $db_prefix;
		$r = new R();
		if($property instanceof ActiveRecordBase){
			$r->table=$db_prefix.strtolower(get_class($property));
			$r->column='id';
		}else
			$r->column=strtolower($property);
		$r->method='=';
		if($isProperty){
			$p=explode('.',$value);
			if(sizeof($p)>1){
				$r->foreigntable=$db_prefix.strtolower($p[0]);
				$r->foreigncolumn=strtolower($p[1]);
			}else
				$r->column=$p[0];
		}else{
			if($value instanceof ActiveRecordBase)
				$r->value=$value->getId();			
			else
				$r->value=$value;
		}
		return $r;		
	}
	static function _And(){
		$r = new R();
		$r->method=' AND ';
		return $r;
	}
	function hasValue(){
		return !empty($this->value);
	}
	function setParameter($param,$value){
		if(strtolower($param)==$this->column)
			$this->value=$value;
	}	
	function getParameter(){
		return array(':'.$this->column=>$this->value);
	}
	function toSQL(){
		switch($this->method){
			case '<>':
			case '=':
				$sql='';	
				if($this->table)
					$sql.=$this->addMark($this->table).'.';
				$sql.=$this->addMark($this->column).$this->method;
				if($this->hasValue())
					$sql.=':'.$this->column;
				else
					$sql.=$this->addMark($this->foreigntable).'.'.$this->addMark($this->foreigncolumn);
				return $sql;
			case ' OR ':
			case ' AND ':
				return $this->method;
		}
	}
	private function addMark($ct){
		return '`'.$ct.'`';
	}
}
?>