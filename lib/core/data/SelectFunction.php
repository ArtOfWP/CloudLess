<?php 
class Avg extends SelectFunction{
	private $as;
	function Avg($column,$as=false){
		$this->setColumn($column);
		$this->as=$as;
	}
	function toSQL($column){
		return "AVG($column) ";
	}
}

abstract class SelectFunction{
	private $column;
	function getColumn(){return $this->column;}
	
	function setColumn($column){$this->column=$column;}
	
	abstract function toSQL($column);
}
?>