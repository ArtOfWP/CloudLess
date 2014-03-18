<?php
use CLMVC\Core\Data\R;

/**
 * Class CountQuery
 * @property array select
 * @property array from
 * @property array where
 * @property array groupby
 *
 */
class CountQuery{
    public $limit;
    public $offset;

    /**
     * Setup a count query. Supply table to preinitate query.
     * @param string $table
     */
    function CountQuery($table=''){
		$this->statement['from']=array();
		$this->statement['order']=array();
		$this->statement['groupby']=array();
		$this->statement['select']=array();			
		$this->statement['where']=array();			
		if($table){
			$this->from($table);	
		}
	}

    /**
     * Create a count query based on table name, classname or object
     * @param string|object $class
     * @return CountQuery
     */
    static function createFrom($class){
		if(is_object($class))
			$class=get_class($class);
		$maintable=strtolower($class);
		$q = new CountQuery($maintable);
		return $q;
	}

    /**
     * Create a count query based on table name
     * @param string $table
     * @return CountQuery
     */
    static function create($table=''){
		return new CountQuery($table);
	}

    /**
     * List of statements required for the query.
     * @var array
     */
    private $statement=array();

    /**
     * Dependencies for the query
     * @var array
     */
    var $depends=array();

    /**
     * @var
     */
    var $returnType;

    /**
     * Use query from
     * @param $table
     * @return $this
     */
    public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark($db_prefix.$table);
		return $this;
	}

    /**
     * Return distinct count query based on supplied property.
     * @param $property
     * @param string $table
     * @return $this
     */
    public function selectDistinct($property,$table=''){
		global $db_prefix;
		$property=$this->addMark($property);
		$this->statement['select'][]=$table?'COUNT( DISTINCT '.$this->addMark($db_prefix.$table).'.'.$property.')':'COUNT( DISTINCT '.$property.')';
		return $this;
	}

    /**
     * Return count query based on supplied property
     * @param $property
     * @param bool $table
     * @return $this
     */
    public function select($property,$table=false){
		global $db_prefix;
		$property=$this->addMark(strtolower($property));
		$this->statement['select'][]=$table?'COUNT('.$this->addMark($db_prefix.$table).'.'.$property.')':'COUNT('.$property.')';
		return $this;
	}

    /**
     * Restrict the count query
     * @param R|R[] $restriction
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
     * Restrict the query with an And separation. Requires a subsequent call to where($restriction) @see where($restriction)
     * @param $restriction
     * @return $this
     */
    public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}

    /**
     * If the query has a where clause.
     * @return bool
     */
    public function hasWhere(){
		return !empty($this->statement['where']);
	}

    /**
     * If the query has a limit clause.
     * @return bool
     */
    public function hasLimit(){
		return !empty($this->limit);
	}

    /**
     * Returns the query statements.
     * @return array
     */
    public function getStatement(){
		return $this->statement;
	}

    /**
     * Group query by property
     * @param $property
     * @return $this
     */
    public function groupBy($property){
		$this->statement['groupby'][]=$this->addMark($property);
		return $this;
	}

    /**
     * Set a parameter
     * @param $param
     * @param $value
     */
    public function setParameter($param, $value){
        /**
         * @var R $restriction
         */
        foreach($this->statement['where'] as $restriction){
			$restriction->setParameter($param,$value);
		}
	}

    /**
     * Executes the query and returns the result.
     * @return int
     */
    function execute(){
		global $db;
		$this->limit=1;
		$this->offset=0;
		$rows=$db->query($this);
		$count=0;
		if(sizeof($rows)>0)
			$count=(int)array_pop($rows[0]);
		return $count;
	}

    /**
     * Get the statements
     * @param $property
     * @return mixed
     */
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
        trigger_error("{$property} is not a valid property of CountQuery", E_USER_WARNING);
        return null;
	}

    /**
     * Add the wrapper for tables.
     * @param $ct
     * @return string
     */
    private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.strtolower($ct).'`';
	}
}
