<?php
/**
 * Class Delete
 * @property array from
 * @property array where
 */
class Delete{
    /**
     * Delete query constructor
     * @param bool $table db table to connect query to
     */
    function Delete($table=false){
		$this->statement['from']=array();	
		$this->statement['where']=array();			
		if($table){
			$this->from($table);
		}
	}

    /**
     * Create a Delete query from a class
     * @param $class
     * @param bool $lazy
     * @return Delete
     */
    static function createFrom($class, $lazy=true){
		if(is_object($class))
			$class=get_class($class);
		$maintable=strtolower($class);
		$d = new Delete($maintable);
		return $d;
	}

    /**
     * Create a Delete query from a table
     * @param bool $table
     * @return Delete
     */
    static function create($table=false){
		return new Delete($table);
	}

    /**
     * @var R[] collection of statements
     */
    private $statement=array();

    /**
     * From part of the query statement
     * @param $table
     * @return $this
     */
    public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark($db_prefix.$table);
		return $this;		
	}

    /**
     * Where restriction
     * @param R[]|R $restriction
     * @return $this
     */
    public function where($restriction){
        if(is_array($restriction)){
            $this->statement['where']=array_merge(((array)$this->statement['where']),$restriction);
        }else
            $this->statement['where'][]=$restriction;
        return $this;
    }

    /**
     * Add restriction and append an And restriction.
     * @param R[]|R $restriction
     * @return $this
     */
    public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}

    /**
     * Checks if query has where clause
     * @return bool
     */
    public function hasWhere(){
		return isset($this->statement['where']) && !empty($this->statement['where']) && sizeof($this->statement['where']) && $this->statement['where'][0]!=null;
	}

    /**
     * Returns the query statements
     * @return array
     */
    public function getStatement(){
		return $this->statement;
	}

    /**
     * Sets a query param
     * @param string $param
     * @param string|int $value
     */
    public function setParameter($param,$value){
        foreach($this->where as $restriction){
			$restriction->setParameter($param,$value);
		}
	}

    /**
     * Executes the query.
     */
    function execute(){
		global $db;
		$db->delete($this);
	}

    /**
     * Gets the statements.
     * @param $property
     * @return R[]
     */
    function __get($property){
		switch($property){
			case 'from':
			case 'where':
				if(isset($this->statement[$property]))
					return $this->statement[$property];
				return array();
		}
	}

    /**
     * @param string $ct
     * @return string
     */
    private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.strtolower($ct).'`';
	}
}