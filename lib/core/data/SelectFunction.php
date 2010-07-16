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
class Max extends SelectFunction{
	private $as;
	function Max($column){
		$this->setColumn(strtolower($column));
	}
	function toSQL($column){
		return "MAX(".strtolower($column).") ";
	}	
}
class Min extends SelectFunction{
	private $as;
	function Min($column){
		$this->setColumn($column);
	}
	function toSQL($column){
		return "MIN($column) ";
	}
}

abstract class SelectFunction{
	private $column;
	function getColumn(){return $this->column;}
	
	function setColumn($column){$this->column=$column;}
	
	abstract function toSQL($column);
}