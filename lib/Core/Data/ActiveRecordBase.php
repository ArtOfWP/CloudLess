<?php
namespace CLMVC\Core\Data;

use CLMVC\Core\Data\Delete;
use CLMVC\Core\Data\Query;
use CLMVC\Core\Data\R;
use CLMVC\Core\Debug;
use CLMVC\Events\Hook;
use CLMVC\Helpers\ObjectUtility;

/**
 * Class ActiveRecordBase
 * @method int getId()
 * @method int setId(int $id)
 * @method setName(string $id)
 * @method string getName()
 */
abstract class ActiveRecordBase {
    /**
     * @var array
     */
    private $tempProperties=array();

    /**
     * Create the object
     */
    function create(){
		Debug::Message('ARB Create '.get_class($this));			
		if(!$this->runEventMethod(__FUNCTION__,'Pre'))
			return;
		$properties =ObjectUtility::getPropertiesAndValues($this);
		$vo=array();
		$vo['table']=strtolower(get_class($this));
		$vo['values']=array();
		foreach($properties as $key =>$value){
			if(isset($value)){
				if($value instanceof ActiveRecordBase) {
                    /**
                     * @var ActiveRecordBase $value
                     */
                    if($value->getId()===false)
						$value->create();
					$value=$value->getId();
				}
                $vo['values'][$key]=$value;
			}
		}
		global $db;
		$id=$db->insert($vo);
		$this->setId($id);
		
		$lists=ObjectUtility::getArrayPropertiesAndValues($this);
		foreach($lists as $list =>$values){
			$settings=ObjectUtility::getCommentDecoration($this,$list.'List');
			$table=array_key_exists_v('dbrelationname',$settings);
			if($values)
				foreach($values as $value){
					if($table && is_subclass_of($value,'ActiveRecordBase')){
						$value->save();
                        $row=array();
						if($value->getId()){
							$col1=strtolower(get_class($value)).'_id';
							$col2=strtolower(get_class($this)).'_id';
							$row['table']=$table;
							$row['values'][$col1]=$value->getId();
							$row['values'][$col2]=$this->getId();
							$db->insert($row);
						}
					}
				}
		}
		$this->runEventMethod(__FUNCTION__,'Post');		
	}

    /**
     * Deletes an object
     */
    function delete(){
		Debug::Message('ARB Delete '.get_class($this));			
		if(!$this->runEventMethod(__FUNCTION__,'Pre'))
			return;
		$lists=ObjectUtility::getArrayPropertiesAndValues($this);
		$column=strtolower(get_class($this)).'_id';
		foreach($lists as $list =>$values){
			$settings=ObjectUtility::getCommentDecoration($this,$list.'List');
			$table=array_key_exists_v('dbrelationname',$settings);
			if($table)
				Delete::createFrom($table)->where(R::Eq($column,$this))->execute();
		}
		Delete::createFrom($this)
		->where(R::Eq($this,$this->getId()))
		->execute();
		$this->runEventMethod(__FUNCTION__,'Post');		
	}

    /**
     * Update the object
     */
    function update(){
		if(!$this->runEventMethod(__FUNCTION__,'Pre'))
			return;
		Debug::Message('ARB Update '.get_class($this));	
		$properties =ObjectUtility::getPropertiesAndValues($this);
		Debug::Value('Properties',$properties);
		$vo=array();
		$vo['table']=strtolower(get_class($this));
		$vo['values']=array();
		foreach($properties as $key =>$value){
			Debug::Value($key,isset($value));
			if($key!='Id' && isset($value)){
				if($value instanceof ActiveRecordBase){					
					Debug::Message($key.' instanceof ARB');
                    $value = $vo['values'][$key]=$value->getId();
				}
                $vo['values'][$key]=$value;
			}
		}
		global $db;
		$db->update($vo,R::Eq($this,$this->getId()));
		
		$lists=ObjectUtility::getArrayPropertiesAndValues($this);
		$col2=strtolower(get_class($this)).'_id';
			
		foreach($lists as $list =>$values){
			$settings=ObjectUtility::getCommentDecoration($this,$list.'List');
			$table=array_key_exists_v('dbrelationname',$settings);
			$existRows=Query::create($table)->selectAll()->where(R::Eq($col2,$this->getId()))->execute();
			$newRows=array();
			Debug::Value('List values',$values);
			if(sizeof($values)>0){
				foreach($values as $value){
					if($table && is_subclass_of($value,'ActiveRecordBase')){
                        /**
                         * @var ActiveRecordBase $value
                         */
                        Debug::Value('Update list',$table);
						$value->save();
						$col1=strtolower(get_class($value)).'_id';
						Debug::Message('Prepare relation insert');
						$insert=true;
						$totalExistRows=sizeof($existRows);
						for($x=0;$x<$totalExistRows;$x++ ){
							$existRow=$existRows[$x];
							if($existRow[$col1]==$value->getId() && $existRow[$col2]==$this->getId()){
								$insert=false;
								$newRows[]=$existRow;
							}
						}
						if($insert){
                            $row=array();
							$row['table']=$table;
							$row['values'][$col1]=$value->getId();
							$row['values'][$col2]=$this->getId();
							$newRows[]=array($value->getId(),$this->getId());
							$db->insert($row);
						}
					}
				}
			}
			foreach($existRows as $existRow)
				if(!in_array($existRow,$newRows)){
                    $rowKeys= array_keys($existRow);
					$col1=array_shift($rowKeys);
					Delete::create($table)->whereAnd(R::Eq($col1,$existRow[$col1]))->where(R::Eq($col2,$existRow[$col2]))->execute();
				}
		}
		$this->runEventMethod(__FUNCTION__,'Post');		
	}

    /**
     * Saves anm object. Updates if existing creates if new.
     */
    function save(){
		Debug::Message('ARB Save '.get_class($this));
		if(!$this->runEventMethod(__FUNCTION__,'Pre'))
			return;
		if($this->getId()>0)
			$this->update();
		else
			$this->create();
		$this->runEventMethod(__FUNCTION__,'Post');
	}

    /**
     * Instantiates an object based on class name.
     * @param string $class
     * @return mixed
     */
    static function _($class){
		$item = new $class();
		return $item;
	}

    /**
     * Returns a property
     * @param string $property
     * @return mixed
     */
    public function __get($property){
		$call="get".$property;
		if(method_exists($this,$call))
			return $this->$call();
		else if(strpos($property,'Lazy')!==false)
			return $this->$call();
		return $this->tempProperties[$property];
	}

    /**
     * Sets an property
     * @param $property
     * @param $value
     * @return mixed
     */
    public function __set($property,$value){
		$call="set".$property;
		if(method_exists($this,$call))
			return $this->$call($value);
		$this->tempProperties[$property]=$value;
        return null;
	}

    /**
     * Checks if an property is set
     * @param $property
     * @return bool
     */
    public function __isset($property) {
        return !empty($this->$property);
    }

    /**
     * Unset an property
     * @param $property
     */
    public function __unset($property) {
    	$property=strtolower($property);
        unset($this->$property);
    }/**/


    /**
     * Call a method. if its ends with Lazy it gets the items from database upon request.
     * If it for example is name getItemList it will retrieve all items and call addItem($item) to add to the list.
     *
     * @param $method
     * @param $arguments
     * @return array
     */
    public function __call($method,$arguments){
		if($this->getId()){
			Debug::Message('ARB __call '.get_class($this).'->'.$method);
			Debug::Value('Arguments',$arguments);
			if(empty($arguments) && strpos($method,'Lazy')!==false){
				$method=str_replace('Lazy','',$method);
				$settings=ObjectUtility::getCommentDecoration($this,$method);
				$foreign=$settings['dbrelation'];
                /**
                 * @var ActiveRecordBase $temp
                 */
                $temp=new $foreign();
				$foreign=strtolower($foreign);
				if(strpos($method,'List')!==false){
					$method=str_replace('get','',$method);									
					$settings=ObjectUtility::getCommentDecoration($this,$method);
					$table=strtolower(get_class($this));

					Debug::Value('Relationname',$settings['dbrelationname']);

					$q=Query::createFrom($temp);
					$q->from($settings['dbrelationname']);
					$q->whereAnd(R::Eq($temp,$settings['dbrelationname'].'.'.$foreign.'_id',true));
					$q->where(R::Eq($table.'_id',$this));
					$list=$q->execute();
					$method='add'.str_replace('List','',$method);
					foreach($list as $li){
						$this->$method($li);
					}
					return $list;
					
				}
                $temp= $temp->getById($this->$method());
                $method=str_replace('get','set',$method);
                $this->$method($temp);
                return $temp;
			}
		}
        if(strpos($method,'set')!==false){
            $method=str_replace('set','',$method);
            $this->$method=array_pop($arguments);
        }
        return array();
	}

    function getById($id) {
        return Repo::getById($this, $id);
    }

    /**
     * Runs the events connected with the method
     * @param $event
     * @param $when
     * @return bool
     */
    private function runEventMethod($event,$when){
		$method='on'.$when.ucfirst($event);
		$class=get_class($this);
		Hook::run($method, array($this));
		Hook::run($class.'->'.$method, array($this));
		if(method_exists($this,$method))
			return $this->$method();
		return true;
	}
}