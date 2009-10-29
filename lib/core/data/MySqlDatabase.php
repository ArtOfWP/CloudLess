<?php
class MySqlDatabase implements IDatabase{
	private $db;
	private $stmt;
	private $relations=array();

	function MySqlDatabase(){
		if(defined('HOST'))
			$this->connect(HOST,DATABASE,USERNAME,PASSWORD);
		else
			$this->connect(DB_HOST,DB_NAME,DB_USER,DB_PASSWORD);
	}
	function connect($host,$database,$username,$password){
		try {
			$this->db = new PDO('mysql:host='.$host.';dbname='.$database, $username, $password);
		} catch (PDOException $e) {
    		print "Error!: " . $e->getMessage() . "<br/>";
    		die();
		}
	}
	function dropTable($object){
		global $db_prefix;
		$table='DROP TABLE `'.$db_prefix.strtolower(get_class($object)).'`';
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
		$this->db->exec($table);
		$arrays = ObjectUtility::getArrayProperties($object);
		foreach($arrays as $array){
			$settings=ObjectUtility::getCommentDecoration($object,$array.'List');
			$relation=array_key_exists_v('dbrelation',$settings);
			if($relation){
				$name=array_key_exists_v('dbrelationname',$settings);
				$class = new $relation;
				if(!array_key_exists($name,$this->relations))
					$this->relations[$name]=array(strtolower(get_class($class).'_id'),strtolower(get_class($object).'_id'));
			}
		}
	}
	function createTable($object){
		global $db_prefix;
		Debug::Value('create table',$db_prefix.strtolower(get_class($object)));
		$table='CREATE TABLE `'.$db_prefix.strtolower(get_class($object)).'` (';
		$columns =	ObjectUtility::getPropertiesAndValues($object);
		Debug::Message('gettings columns');
		foreach($columns as $property => $value){
			$column=strtolower($property);
			$table.=' `'.$column.'` ';
			if($column=='id')
				$table.='INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,';
			else{
				$settings=ObjectUtility::getCommentDecoration($object,'get'.$property);				
				if(!isset($value)){
					$dbfield=strtolower(array_key_exists_v('dbfield',$settings));					
					if($dbfield=='char'){
						$length=array_key_exists_v('dblength',$settings);
						if($length)
							$table.='VARCHAR('.$length.') NOT NULL default \'\',';
						else{
							$table.='VARCHAR(45) NOT NULL default \'\',';
						}						
					}else if($dbfield=='text')
						$table.='TEXT NOT NULL,';
					else if($dbfield=='int')
						$table.='INTEGER NOT NULL default 0,';					
					else{
						$table.='VARCHAR(45) NOT NULL default \'\',';
					}
				}
				else if(is_int($value)){
					$table.='INTEGER NOT NULL default '.$value.',';
				}
				else if(is_string($value)){
					$dbfield=array_key_exists_v('dbfield',$settings);
					if($dbfield=='text')
						$table.='TEXT NOT NULL,';
					else{
						$length=array_key_exists_v('dblength',$settings);
						if($length)
							$table.='VARCHAR('.$length.') NOT NULL default \''.$value.'\',';
						else{
							$table.='VARCHAR(45) NOT NULL default \''.$value.'\',';
						}
					}
				}
			}
		}
		$table=rtrim($table,",");
		$table.=') ENGINE InnoDB';
		
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
		$this->db->exec($table);


		$arrays = ObjectUtility::getArrayProperties($object);
		foreach($arrays as $array){
			$settings=ObjectUtility::getCommentDecoration($object,$array.'List');
			$relation=array_key_exists_v('dbrelation',$settings);
			if($relation){
				$name=array_key_exists_v('dbrelationname',$settings);
				$class = new $relation;
				if(!array_key_exists($name,$this->relations))
					$this->relations[$name]=array(strtolower(get_class($class).'_id'),strtolower(get_class($object).'_id'));
			}
		}		
	}
	
	function insert($row){
		if(is_array($row)){
			global $db_prefix;
			$prepared='INSERT INTO `'.$db_prefix.strtolower($row['table']).'`';
			$colval=$row['values'];
			foreach($colval as $column => $value)
				if(!empty($value)){
					$column=strtolower($column);
					$columns[]='`'.$column.'`';
					$params[]=':'.$column;
					$values[':'.$column]=$value;
				}
			$prepared.=' ('.implode(',',$columns).') ';
			$prepared.=' VALUES('.implode(',',$params).')';
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
			$stmt=$this->db->prepare($prepared);
		    Debug::Value('SQL',$prepared);
		    Debug::Value('SQL Params',$params);
			if (!$stmt) {
				Debug::Value('Error occured when preparing sql statement',$prepared);				
	    		Debug::Value('SQL Params',$values);				
			    Debug::Value('PDO::errorInfo()',$this->db->errorInfo());
			}
						
			if (!$stmt->execute($values)) {
				Debug::Value('Error with sql statement',$prepared);				
			    Debug::Value('SQL Params',$values);
				Debug::Message('PDO::errorInfo()');print_r($this->db->errorInfo());				
			}				
		}else
			die('MySqlDatabase->insert only accepts arrays. See documentation for structure');
		return (int)$this->db->lastInsertId();
	}
	function update($row,$restriction){
		if(is_array($row)){
			global $db_prefix;
			$prepared='UPDATE `'.$db_prefix.strtolower($row['table']).'` ';
			$colval=$row['values'];
			foreach($colval as $column => $value){
//				if(!empty($value)){
					$column=strtolower($column);
					$columns[]="$column=:$column";					
					$values[':'.$column]=$value;
				}
			$values=array_merge($values,$restriction->getParameter());
			$prepared.=' SET '.implode(',',$columns).' ';
			$prepared.=' WHERE '.$restriction->toSQL();
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
			$stmt=$this->db->prepare($prepared);
			Debug::Value('Sql statement',$prepared);				
    		Debug::Value('SQL Params',$values);	
			if (!$stmt) {
				Debug::Value('Error occured when preparing sql statement',$prepared);				
	    		Debug::Value('SQL Params',$values);				
			    Debug::Value('PDO::errorInfo()',$this->db->errorInfo());
			}
						
			if (!$stmt->execute($values)) {
				Debug::Value('Error with sql statement',$prepared);				
			    Debug::Value('SQL Params',$values);
				Debug::Message('PDO::errorInfo()');print_r($this->db->errorInfo());				
			}
		}else
			die('MySqlDatabase->update only accepts arrays. See documentation for structure');
//		return (int)$this->db->lastInsertId();
	}
	function query($q){
		$from=implode(',',$q->from);
	    $columns=implode(',',$q->select);
	    $where='';
	    $params=array(); 
	    if($q->hasWhere())
	    {
			foreach($q->where as $clause){
				
				$where.=$clause->toSQL();
				if($clause->method==' IN ')
					$params=array_merge($params,$clause->getParameters());				
				else if($clause->hasValue()){
					$param=$clause->getParameter();
					Debug::Value('Param',$param);
					$params=array_merge($params,$param);
				}
			}
	    }
		if($where)
			$where =' WHERE '.$where;
	    $prepared='SELECT '.$columns.' FROM '.$from.$where;
	    if(defined('SQLDEBUG') && SQLDEBUG){
		    Debug::Value('SQL',$prepared);
		    Debug::Value('SQL Params',$params);
	    }

	    $stmt=$this->db->prepare($prepared);
			if (!$stmt) {
				Debug::Value('Error occured when preparing sql statement',$prepared);				
	    		Debug::Value('SQL Params',$params);				
			    Debug::Value('PDO::errorInfo()',$this->db->errorInfo());
			}
						
			if (!$stmt->execute($params)) {
				Debug::Value('Error with sql statement',$prepared);				
			    Debug::Value('SQL Params',$params);
				Debug::Value('PDO::errorInfo()',$this->db->errorInfo());				
			}	
		$result=$stmt->fetchAll($fetch_style=PDO::FETCH_ASSOC);
		return $result;
	}
	function delete($d){
		$from=implode(',',$d->from);
	    $where='';
	    $params=array(); 
	    if($d->hasWhere())
	    {
			foreach($d->where as $clause){		
				$where.=$clause->toSQL();
				if($clause->hasValue()){
					$param=$clause->getParameter();
					$params=array_merge($params,$param);
				}
			}
	    }
		if($where)
			$where =' WHERE '.$where;
	    $prepared='DELETE FROM '.$from.$where;
	    Debug::Value('SQL',$prepared);
	    Debug::Value('SQL Params',$params);
	    $stmt=$this->db->prepare($prepared);
			if (!$stmt) {
				Debug::Value('Error occured when preparing sql statement',$prepared);				
			    Debug::Value('SQL Params',$params);
				Debug::Value('PDO::errorInfo()',$this->db->errorInfo());
			}
						
			if (!$stmt->execute($params)) {
				Debug::Value('Error occured when executing sql statement',$prepared);				
			    Debug::Value('SQL Params',$params);
				Debug::Value('PDO::errorInfo()',$this->db->errorInfo());
			}			
	}
	private function bindParams(&$stmt,$param,$value){
		$stmt->bindParam($param,$value);
	}
	
	function close(){
		$this->db=null;
	}
	function createStoredRelations(){
		global $db_prefix;
		$relations=$this->relations;
		foreach($relations as $table => $columns){
			$table=' CREATE TABLE `'.$db_prefix.$table.'` ( `'.$columns[0].'` INTEGER NOT NULL, `'.$columns[1].'` INTEGER NOT NULL)';
			$this->db->exec($table);
		}
	}
	function dropStoredRelations(){
		global $db_prefix;
		$relations=$this->relations;
		foreach($relations as $table => $columns){
			$table='DROP TABLE `'.$db_prefix.$table.'`';
			$this->db->exec($table);
		}
	}
}
?>