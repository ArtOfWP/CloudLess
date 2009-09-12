<?php
class SelectStatement{
	private $statement=array();
	
	public function from($table){
		$this->statement['from'][]=$table;
	}
	public function select($property){
		$this->statement['select'][]=strtolower($property);
	}
	public function where($restriction){
		$this->statement['where'][]=$restriction;
	}
	public function getStatement(){
		
		return $this->statement;
	}
}
?>