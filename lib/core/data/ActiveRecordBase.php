<?php
abstract class ActiveRecordBase{
	function create(){
		$properties =ObjectUtility::getPropertiesAndValues($this);
		$vo=array();
		$vo['table']=strtolower(get_class($this));
		$vo['values']=array();
		foreach($properties as $key =>$value){
			if(isset($value)){
				if($value instanceof ActiveRecordBase){					
					$value->create();
					$vo['values'][$key]=$value->getId();										
				}else{
					$vo['values'][$key]=$value;
				}
			}
		}
//		Debug::Message('<strong>Create '.$vo['table'].'</strong>');
//		Debug::Value('Values',$vo['values']);
		global $db;
		$id=$db->insert($vo);
		$this->setId($id);
		
		$lists=ObjectUtility::getArrayPropertiesAndValues($this);
		$voDependant=array();
		foreach($lists as $list =>$values){
			$settings=ObjectUtility::getCommentDecoration($this,$list.'List');
			$table=array_key_exists_v('dbrelationname',$settings);
			if($values)
				foreach($values as $value){
					if($table && is_subclass_of($value,'ActiveRecordBase')){
						$value->create();
						$col1=strtolower(get_class($value)).'_id';
						$col2=strtolower(get_class($this)).'_id';
						$row['table']=$table;
						$row['values'][$col1]=$value->getId();
						$row['values'][$col2]=$this->getId();
						$db->insert($row);
						$row=null;
					}	
				}
		}
	}
	function delete(){
		Delete::createFrom($this)
		->where(R::Eq($this,$this->getId()))
		->execute();
	}
	function update(){}
	function save(){
		if($this->getId()>0)
			$this->update();
		else
			$this->create();
	}
	static function _($class){
		$item = new $class();
		return $item;
	}
	
	protected function p_findByProperty($property,$value,$lazy=false){
		return Query::createFrom($this,$lazy)->where(R::Eq($property,$value))->execute();
	}
	protected function p_slicedFindAll($firstResult,$maxResult,$order,$restrictions){
		return Query::createFrom($this)
			->limit($firstResult,$maxResult)
			->orderBy($order)
			->where($restrictions)
			->execute();
	}
	
	protected function p_findAll(){
		return Query::createFrom($this)->execute();

	}
	protected function p_getById($id,$lazy=false){

		$objects= Query::createFrom($this,$lazy)
				  ->where(R::Eq($this,$id))
				  ->execute();
		return sizeof($objects)==1?$objects[0]:false;
	}
	public function __call($method,$arguments){
		if($this->getId()){
			if(empty($arguments)){
				$method=str_replace('Lazy','',$method);
				$settings=ObjectUtility::getCommentDecoration($this,$method);
				$foreign=$settings['dbrelation'];
				$temp=new $foreign();
				
				if(strpos($method,'List')!==false){
					$method=str_replace('get','',$method);									
					$settings=ObjectUtility::getCommentDecoration($this,$method);
					$foreign=$settings['dbrelation'];
					$stmt=new SelectStatement();
					$table=strtolower(get_class($this));
					$stmt->From($foreigntable);
					$stmt->From($this);
					$properties =ObjectUtility::getProperties(new $foreigntable);
					
					foreach($properties as $property)
						$stmt->Select($foreigntable.'.'.strtolower($property));
						$stmt->From($settings['dbrelationname']);
						$stmt->Where(R::Eq($this,$table.'_id'));
//						$stmt->Where(R::Eq());
//						$db->select($select);
				}else{
					$temp= $temp->getById($this->$method());
					$method=str_replace('get','set',$method);
					$this->$method($temp);
					return $temp;
				}
			}
		}
	}
}