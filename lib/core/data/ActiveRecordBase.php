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
	function update(){		
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
		$db->update($vo,R::Eq($this,$this->getId()));
		
		$lists=ObjectUtility::getArrayPropertiesAndValues($this);
		$voDependant=array();
		foreach($lists as $list =>$values){
			$settings=ObjectUtility::getCommentDecoration($this,$list.'List');
			$table=array_key_exists_v('dbrelationname',$settings);
			if($values){
				foreach($values as $value){
					if($table && is_subclass_of($value,'ActiveRecordBase')){
						$value->update();
/*						$col1=strtolower(get_class($value)).'_id';
						$col2=strtolower(get_class($this)).'_id';
						$row['table']=$table;
						$row['values'][$col1]=$value->getId();
						$row['values'][$col2]=$this->getId();
						$db->update($row);
						$row=null;*/
					}	
				}
			}
		}
	}
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
//					$foreign=$settings['dbrelation'];
//					$stmt=new SelectStatement();
					$table=strtolower(get_class($this));
/*					$stmt->From($foreigntable);
					$stmt->From($this);*/
					$properties =ObjectUtility::getProperties(new $foreigntable);
					
/*					foreach($properties as $property)
						$stmt->Select($foreigntable.'.'.strtolower($property));
						$stmt->From($settings['dbrelationname']);
						$stmt->Where(R::Eq($this,$table.'_id'));*/
//						$stmt->Where(R::Eq());
//						$db->select($select);
// select * from item, company,relation WHERE item.id = relation.item_id AND company.id=relation.company_id
					$q=Query::createFrom($foreigntable);
					foreach($properties as $property)
						$q->select($property,$foreigntable);/**/
					$q->from($settings['dbrelationname']);
					$q->whereAnd(R::Eq(new $foreigntable,$foreigntable.'_id'));
					$q->where(R::Eq($this,$table.'_id'));
					
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