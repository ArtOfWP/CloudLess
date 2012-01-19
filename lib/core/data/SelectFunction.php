<?php 
class Avg extends SelectFunction{
	private $as;
	function Avg($column,$as=false){
		$this->setColumn($column);
		$this->as=$as;
	}
	function toSQL($column){
		return "AVG($column) ".($this->as?' as '.$this->as:'');
	}
}
class Max extends SelectFunction{
	private $as;
	function Max($column,$as=false){
		$this->setColumn(strtolower($column));
        $this->as=$as;
	}
	function toSQL($column){
		return "MAX(".strtolower($column).") ".($this->as?' as '.$this->as:'');
	}	
}
class Min extends SelectFunction{
	private $as;
	function Min($column,$as=false){
		$this->setColumn($column);
        $this->as=$as;
	}
	function toSQL($column){
		return "MIN($column) ".($this->as?' as '.$this->as:'');
	}
}

abstract class SelectFunction{
	private $column;
	function getColumn(){return $this->column;}
	
	function setColumn($column){$this->column=$column;}
	
	abstract function toSQL($column);
}