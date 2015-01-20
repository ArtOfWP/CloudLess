<?php
namespace CLMVC\Core\Data;
use CLMVC\Core\Debug;
use CLMVC\Helpers\ObjectUtility;

/**
 * Class Query
 */
class Query{
    /**
     * @var int limit query
     */
    public $limit;
    /**
     * @var int offset query
     */
    public $offset;
    /**
     * @var array query statements
     */
    private $statement=array();
    /**
     * @var array list of objects that the main object/class depends on
     */
    public $depends=array();
    /**
     * @var string the type of return of select query
     */
    public $returnType;
    /**
     * @var array list of dependencies
     */
    public $dependslist=array();

    /**
     * Create a query
     * @param string $table
     */
    function __construct($table=''){
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
     * Create a query based on a class or an instantiated class, i.e object. Lazy preloads the return object.
     * @param object|string $class
     * @param bool $lazy
     * @throws \InvalidArgumentException
     * @return Query
     */
    static function createFrom($class,$lazy=false){
		if(is_object($class))
			$class=get_class($class);
		$main_table=strtolower($class);
		$query = new Query($main_table);
        if(class_exists($class)) {
            $query->returnType = $class;
            $object = new $class();
        } else {
            throw new \InvalidArgumentException('Provided class is not an object or a class that can be instantiated');
        }
		Debug::Value('createFrom',$class);
		Debug::Backtrace();
        self::setup_one_to_one_dependencies($lazy, $object, $query, $main_table);
        self::setup_one_to_many_dependencies($class, $lazy, $object, $query);
		return $query;
	}

    /**
     * Create query based on table.
     * @param string $table
     * @return Query
     */
    static function create($table=''){
		Debug::Value('Query create', $table);
		Debug::Backtrace();
		return new Query($table);
	}

    /**
     * Set from, adds new from per call
     * @param $table
     * @return $this
     */
    public function from($table){
		global $db_prefix;
		$this->statement['from'][]=$this->addMark(strtolower($db_prefix.$table));
		return $this;
	}

    /**
     * @param bool $lazy
     * @param object $object
     * @param Query $query
     * @param string $main_table
     */
    private static function setup_one_to_one_dependencies($lazy, $object, $query, $main_table) {
        $properties = ObjectUtility::getProperties($object);
        foreach ($properties as $property) {
            $depends_on = array_key_exists_v('dbrelation', ObjectUtility::getCommentDecoration($object, 'get' . $property));
            if ($depends_on && $lazy) {
                Debug::Value('dbrelation', $depends_on);
                $temp = new $depends_on();
                $gProperty = Query::createFrom($temp)->where(R::Eq($temp, 'Id'));
                $query->depends[$property] = $gProperty;
            }
            $query->select($property, $main_table);
        }
    }

    /**
     * @param string $class
     * @param bool $lazy
     * @param object $object
     * @param Query $query
     */
    private static function setup_one_to_many_dependencies($class, $lazy, $object, $query)
    {
        $arrays = ObjectUtility::getArrayProperties($object);
        if ($arrays && $lazy) {
            Debug::Message('Generating array queries');
            Debug::Value('Arrays', $arrays);
            $query->dependslist = array();
            foreach ($arrays as $array) {
                $dependson = array_key_exists_v('dbrelation', ObjectUtility::getCommentDecoration($object, $array . 'List'));
                if ($dependson) {
                    Debug::Message($class . ' ' . $array . ' has relations');
                    Debug::Value('dbrelation', $dependson);
                    $dbrelationname = array_key_exists_v('dbrelationname', ObjectUtility::getCommentDecoration($object, $array . 'List'));
                    $temp = new $dependson();
                    Debug::Value('Relationname', $dbrelationname);
                    $qList = Query::createFrom($temp);
                    $qList->from($dbrelationname);
                    $qList->whereAnd(R::Eq($temp, $dbrelationname . '.' . $dependson . '_id', true));
                    $query->dependslist[$array] = $qList;
                }
            }
        }
    }

    /**
     * Adds a distinct select either a string based property or a select function. New calls adds new distincts
     * @param string|SelectFunction $property
     * @param string $table
     * @return $this
     */
    public function selectDistinct($property,$table=null){
		global $db_prefix;
		if($property instanceof SelectFunction){
			$this->statement['select'][]='DISTINCT '.$property->toSQL(strtolower($this->addMark($property->getColumn())));
            return $this;
		}
        $property=$this->addMark(strtolower($property));
        $this->statement['select'][]=strtolower($table?'DISTINCT '.$this->addMark($db_prefix.$table).'.'.$property:'DISTTINCT '.$property);
		return $this;
	}

    /**
     * Adds a select all columns
     * @return $this
     */
    public function selectAll(){
		$this->statement['select'][]='*';
		return $this;
	}

    /**
     * Add select
     * @param string|SelectFunction $property
     * @param string $table
     * @return $this
     */
    public function select($property,$table=''){
		global $db_prefix;
		if($property instanceof SelectFunction){
			$this->statement['select'][]=$property->toSQL($this->addMark($property->getColumn()));
            return $this;
        }
        $property=$this->addMark(strtolower($property));
		$this->statement['select'][]=strtolower($table?$this->addMark($db_prefix.$table).'.'.$property:$property);
		return $this;
	}

    /**
     * Add where clause
     * @param R|R[]$restriction
     * @return $this
     */
    public function where($restriction){
		if(is_array($restriction)){
			$this->statement['where']=array_merge(((array)$this->statement['where']),$restriction);
            return $this;
        }
        $this->statement['where'][]=$restriction;
		return $this;
	}

    /**
     * Offset and limit query
     * @param int $offset
     * @param int $limit
     * @return $this
     */
    public function limit($offset,$limit=0){
        if($limit)
            $this->limit=$limit;
        $this->offset=$offset;
		return $this;
	}

    /**
     * Adds an R::_And() restriction before adding restriction
     * @param R|R[] $restriction
     * @return $this
     */
    public function And_($restriction){
		$this->statement['where'][]=R::_And();
		$this->where($restriction);
		return $this;
	}

    /**
     * Adds an R::_Or() restriction before adding provided restriction
     * @param R|R[] $restriction
     * @return $this
     */
    public function Or_($restriction){
		$this->statement['where'][]=R::_Or();
		$this->where($restriction);
		return $this;
	}

    /**
     * Adds an R::_And() restriction after adding restriction
     * @param R|R[] $restriction
     * @return $this
     */
    public function whereAnd($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_And();
		return $this;
	}

    /**
     * Adds an R::_Or() restriction after adding provided restriction
     * @param R|R[] $restriction
     * @return $this
     */
    public function whereOr($restriction){
		$this->where($restriction);
		$this->statement['where'][]=R::_Or();
		return $this;
	}

    /**
     * Adds an order
     * @param Order|Order[] $order
     * @return $this
     */
    public function order($order){
		if(is_array($order)) {
            $this->statement['order'] = ((array)$this->statement['order']) + $order;
            return $this;
        }
    	$this->statement['order'][]=$order;
		return $this;
	}

    /**
     * Checks if where clause is set
     * @return bool
     */
    public function hasWhere(){
		return isset($this->statement['where']) && !empty($this->statement['where']) && sizeof($this->statement['where']) && $this->statement['where'][0]!=null;
	}

    /**
     * Checks if query is limited
     * @return bool
     */
    public function hasLimit(){
		return !empty($this->limit);
	}

    /**
     * Returns statements
     * @return array
     */
    public function getStatement(){
		return $this->statement;
	}

    /**
     * Splits an property into table and column
     * @param $property
     * @return string
     */
    private function splitAndMark($property){
        global $db_prefix;
        $select=explode('.',$property);
        if(sizeof($select)>1)
            $select[0]=$db_prefix.$select[0];
        $columns=array();
        foreach($select as $s)
            $columns[]=$this->addMark(strtolower($s));
        return implode('.',$columns);
    }

    /**
     * Groups query
     * @param string|string[] $property
     * @return $this
     */
    public function groupBy($property){
		if(is_array($property)) {
            foreach ($property as $p)
                $this->statement['groupby'][] = $this->splitAndMark($p);
            return $this;
        }
    	$this->statement['groupby'][]=$this->splitAndMark($property);
		return $this;
	}

    /**
     * Set parameter
     * @param string $param
     * @param $value
     */
    public function setParameter($param,$value){
        /**
         * @var R $restriction
         */
        foreach($this->statement['where'] as $restriction){
			$restriction->setParameter($param,$value);
		}
	}

    /**
     * Executes query
     * @return array
     */
    function execute(){
		global $db;
		$rows=$db->query($this);
		Debug::Value('Rows returned',sizeof($rows));
		if(isset($this->returnType)) {
            return $this->setup_return_objects($rows);
		} else
			return $rows;
	}

    /**
     * @param $property
     * @return array
     */
    function __get($property){
		switch($property){
			case 'from':
			case 'select':
			case 'where':
			case 'order':
			case 'groupby':
				if(isset($this->statement[$property]))
					return $this->statement[$property];
		}
        trigger_error("$property is not a valid property for Query", E_USER_WARNING);
        return array();
	}

    /**
     * @param $ct
     * @return string
     */
    private function addMark($ct){
		$ct=trim($ct,"`");
		return '`'.$ct.'`';
	}

    /**
     * @param $rows
     * @return array
     */
    private function setup_return_objects($rows) {
        $class = $this->returnType;
        $objects = array();
        if (sizeof($rows) > 0) {
            foreach ($rows as $row) {
                $object = new $class();
                ObjectUtility::setProperties($object, $row);
                if (sizeof($this->depends) > 0) {
                    $this->setup_one_to_one_return_type($object);
                }
                if (sizeof($this->dependslist) > 0) {
                    $this->setup_one_to_many_return_object($class, $object);
                }
                if (method_exists($object, 'getId'))
                    $objects[$object->getId()] = $object;
                else
                    $objects[] = $object;
            }
        }
        return $objects;
    }

    /**
     * Setups a one to one
     * @param object $object
     */
    private function setup_one_to_one_return_type($object) {
        Debug::Message('Object property depends on');
        /**
         * @var string $property
         * @var Query $query
         */
        foreach ($this->depends as $property => $query) {
            $getproperty = 'get' . $property;
            $value = (int)$object->$getproperty();
            if ($value) {
                $query = clone $query;
                $query->setParameter('Id', $value);
                $value = $query->execute();
                if ($value)
                    ObjectUtility::setProperties($object, array($property => array_shift($value)));
            }
            $query = null;
        }
    }

    /**
     * Setups one to many object
     * @param string $class
     * @param $object
     */
    private function setup_one_to_many_return_object($class, $object) {
        Debug::Message('Object list depends on');
        $dependslist = $this->dependslist;
        Debug::Value('Dependings', $dependslist);
        /**
         * @var string $property
         * @var Query $query
         */
        foreach ($dependslist as $property => $query) {
            Debug::Value('Property', $property);
            $query = clone $query;
            $query->where(R::Eq(strtolower($class) . '_id', $object));
            Debug::Value('Query', $query);
            $values = $query->execute();
            $query = null;
            if ($values)
                ObjectUtility::addToArray($object, $property, $values);
        }
    }
}