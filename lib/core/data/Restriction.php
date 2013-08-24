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

    /**
     * Match keywords against columns
     * @param $columns
     * @param $keywords
     * @return R
     */
    static function Match($columns, $keywords){
		$r = new R();
		$r->method='MATCH';
		$r->columns=$columns;
		$r->setParameter('matchParams'.sizeof($columns),trim($keywords));
		return $r;
	}

    /**
     * Greater than or equal to
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty value is an ActiveRecordBase object use property
     * @return R
     */
    static function Ge($property, $value, $isProperty=false){
		$r=R::Eq($property, $value, $isProperty);
		$r->method='>=';
		return $r;
	}

    /**
     * Less than or equal too
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
    static function Le($property, $value, $isProperty=false){
		$r=R::Ge($property, $value, $isProperty);
		$r->method='<=';
		return $r;
	}

    /**
     * Less than
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
    static function Lt($property, $value, $isProperty=false){
		$r=R::Eq($property,$value,$isProperty);
		$r->method='<';
		return $r;
	}

    /**
     * Greater than
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
    static function Gt($property, $value, $isProperty=false){
		$r=R::Eq($property,$value,$isProperty);
		$r->method='>';
		return $r;
	}

    /**
     * Equal too
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
    static function Eq($property,$value, $isProperty=false){
		global $db_prefix;
		$r = new R();
		if($property instanceof ActiveRecordBase){
			$r->table=$db_prefix.strtolower(get_class($property));
			$r->column='Id';
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
			$r->setParameter(str_replace('.','',$r->column),$r->value);
		}		
		return $r;		
	}

    /**
     * Not equal too
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
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

    /**
     * Equal too compares ActiveRecordBase
     * @param $class
     * @param string $property column to check against
     * @param mixed $value value to compare against
     * @param bool $isProperty
     * @return R
     */
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

    /**
     * column value in list of values
     * @param $property
     * @param $values
     * @return R
     */
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

    /**
     * Like
     * @param $property
     * @param $value
     * @param int $placement
     * @return R
     */
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

    /**
     * AND restriction
     * @return R
     */
    static function _And(){
		$r = new R();
		$r->method=' AND ';
		return $r;
	}

    /**
     * OR restriction
     * @return R
     */
    static function _Or(){
		$r = new R();
		$r->method=' OR ';
		return $r;
	}

    /**
     * If has value
     * @return bool
     */
    function hasValue(){
		return $this->hasvalue;
	}

    /**
     * Retrieve value
     * @return mixed
     */
    function getValue(){
		return $this->value;
	}

    /**
     * Set parameter
     * @param $param
     * @param $value
     */
    function setParameter($param,$value){
		Debug::Value('Set param :'.$param, $value);
		$this->parameters[":$param"]=$value;
	}

    /**
     * remove parameter
     * @param $param
     */
    function removeParameter($param){
		$param=trim($param,':');
		unset($this->parameters[":$param"]);		
	}

    /**
     * Return parameter
     * @param bool $key
     * @return array
     */
    function getParameter($key=false){
		if($key)
			return $this->parameters[":$key"];
		return $this->parameters;
	}

    /**
     * Return parameters
     * @return array
     */
    function getParameters(){
		return $this->parameters;
	}

    /**
     * Convert restriction to SQL counterpart
     * @return string
     */
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

    /**
     * @param $ct
     * @return string
     */
    private function addMark($ct){
		return '`'.strtolower($ct).'`';
	}

    const BOTH = 1;
    const LEFT = 0;
    const RIGHT=2;
}