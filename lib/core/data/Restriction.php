<?php
class R{
	var $table;
	var $column;
	var $foreigntable;
	var $foreigncolumn;
	var $value;
	var $hasvalue=false;
	var $values=array();
	var $parameters=array();
	var $columns;
	var $method;
	var $placement;

	static $LEFT=0;
	static $BOTH=1;
	static $RIGHT=2;
		
	static function Match($columns,$keywords){
		$r = new R();
		$r->method='MATCH';
		$r->columns=$columns;
//		$r->value=trim($keywords);
		$r->setParameter('matchParams'.sizeof($columns),trim($keywords));
//		$r->param=str_replace(array(' ','-','+'),'',trim($r->value));
		return $r;
	}
	static function Ge($property,$value,$isProperty=false){
		$r=R::Eq($property,$value,$isProperty);
		$r->method='>=';
		return $r;
	}
	static function Le($property,$value,$isProperty=false){
		$r=R::Ge($property,$value,$isProperty);
		$r->method='<=';
		return $r;
	}
	
	static function Lt($property,$value,$isProperty=false){
		$r=R::Eq($property,$value,$isProperty);
		$r->method='<';
		return $r;
	}
	static function Gt($property,$value,$isProperty=false){
		$r=R::Eq($property,$value,$isProperty);
		$r->method='>';
		return $r;
	}
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
			$r->hasvalue=true;
			$r->setParameter($r->column,$r->value);
		}		
		return $r;		
	}
	static function NotEq($property,$value,$isProperty=false){
		global $db_prefix;
		$r = new R();
		if($property instanceof ActiveRecordBase){
			$r->table=$db_prefix.strtolower(get_class($property));
			$r->column='id';
		}else
			$r->column=strtolower($property);
		$r->method='<>';
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
			$r->hasvalue=true;
			$r->setParameter($r->column,$r->value);			
		}
		return $r;		
	}	
	static function EqP($class,$property,$value,$isProperty=false){
		global $db_prefix;
		$r = new R();
		if($class instanceof ActiveRecordBase){
			$r->table=$db_prefix.strtolower(get_class($class));
			$r->column=$property;
		}else{
			$r->table=$db_prefix.strtolower($class);
			$r->column=$property;
		}
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
			$r->hasvalue=true;
			$r->setParameter($r->column,$r->value);
		}
		return $r;		
	}	
	static function In($property,$values){
		global $db_prefix;
		$r = new R();
		if($property instanceof ActiveRecordBase){
			$r->table=$db_prefix.strtolower(get_class($property));
			$r->column='id';
		}else
			$r->column=strtolower($property);
		$r->method=' IN ';
/*		if($isProperty){
			$p=explode('.',$value);
			if(sizeof($p)>1){
				$r->foreigntable=$db_prefix.strtolower($p[0]);
				$r->foreigncolumn=strtolower($p[1]);
			}else
				$r->column=$p[0];
		}else{*/
		$count=0;
		foreach($values as $value){
			if($value instanceof ActiveRecordBase)
				$tempValue=$value->getId();
			else
				$tempValue=$value;
			$r->values[]=$tempValue;
			$param=$r->column.($count++);
			$r->setParameter($param,$tempValue);
		}
//		}
		return $r;	
	}
	static function Like($property,$value,$placement=0){
		$r = new R();
		$r->column=strtolower($property);
		$r->method='LIKE';
		$r->placement=$placement;
		$r->value=$value;
		$r->setParameter($r->column,$value);
		$r->hasvalue=true;
		return $r;
	}
	static function _And(){
		$r = new R();
		$r->method=' AND ';
		return $r;
	}
	static function _Or(){
		$r = new R();
		$r->method=' OR ';
		return $r;
	}	
	function hasValue(){
		return $this->hasvalue;
	}
	function getValue(){
		return $this->value;
	}
	function setParameter($param,$value){
		$this->parameters[":$param"]=$value;
	}
	function getParameter($key=false){
		if($key)
			return $this->parameters[":$key"];
		return $this->parameters;
	}
	function getParameters(){		
		return $this->parameters;
	}
	function toSQL(){
		switch($this->method){
			case "LIKE":
				if($this->placement==R::$LEFT)
					$front="%";
				else if($this->placement==R::$RIGHT)
					$back="%";
				else{
					$front="%";					
					$back="%";
				}
				$param=array_pop(array_keys($this->getParameters()));
				$sql=$this->addMark($this->column).' LIKE '."concat('$front',".$param.",'$back')";
				return $sql;
			case 'MATCH':
				$sql=' MATCH(';
				$count=count($this->columns);
				$columns=$this->columns;
				for($i=0;$i<$count;$i++)
					$columns[$i]=$this->addMark($columns[$i]);
				
					$sql.=implode(',',$columns);
				$sql.=') AGAINST(';
				$param=array_pop(array_keys($this->getParameters()));				
				$sql.=$param;
				$sql.=' IN BOOLEAN MODE)';
				return $sql;
			case ' IN ':
				$sql='';
				if($this->table)
					$sql.=$this->addMark($this->table).'.';
				$sql.=$this->addMark($this->column).$this->method;
				$sql.='(';
				$sql.=implode(',',array_keys($this->getParameters()));
				$sql.=')';
/*				if($this->hasValue())
					$sql.=':'.$this->column;
				else
					$sql.=$this->addMark($this->foreigntable).'.'.$this->addMark($this->foreigncolumn);*/
				return $sql;
			case '>=':
			case '>':
			case '<=':
			case '<':
			case '<>':
			case '=':
				$sql='';	
				if($this->table)
					$sql.=$this->addMark($this->table).'.';
				$sql.=$this->addMark($this->column).$this->method;
				if($this->hasValue())
					$sql.=array_pop(array_keys($this->getParameters()));
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