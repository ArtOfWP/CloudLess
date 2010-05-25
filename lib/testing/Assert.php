<?php
class Assert{
	private $pass;
	private $testitem;
	public $message;
	private $inUse;
	function Assert($testitem){
		$this->testitem=$testitem;
	}
	static function That($testitem){
		$a = new Assert($testitem);
		return $a;
	}
	public function IsTrue(){
		if($this->testitem)
			$this->pass=true;
		else
			$this->pass=false;
		return $this;
	}
	public function IsFalse(){
		if(!$this->testitem)
			$this->pass=true;
		else
			$this->pass=false;
		return $this;
	}
	public function Equals($compare){
		($this->testitem==$compare)?$this->pass=true:$this->pass=false;
		return $this;		
	}
	public function Contains($value){
		if(is_array($this->testitem))
			in_array($value,$this->testitem)?$this->pass=true:$this->pass=false;
		else if(is_string($testitem))
			strstr($this->testitem,$value)?$this->pass=true:$this->pass=false;
		return $this;			
	}
	public function KeyExist($key){
		if(is_array($this->testitem))
			array_key_exists($key,$this->testitem)?$this->pass=true:$this->pass=false;
		else
			die("Test item ain't an array");
		return $this;
	}
	public function KeyDoesNotExist($key){
		if(is_array($this->testitem))
			!array_key_exists($key,$this->testitem)?$this->pass=true:$this->pass=false;
		else
			die("Test item ain't an array");
		return $this;
	}	
	public function DoNotContain($value){
		if(is_array($this->testitem))
			!in_array($value,$this->testitem)?$this->pass=true:$this->pass=false;
		else if(is_string($testitem))
			!strstr($this->testitem,$value)?$this->pass=true:$this->pass=false;		
		return $this;
	}
	public function Message($message){
		$this->message=$message;
		return $this;
	}
	public function Passed(){
		return $this->pass;
	}
}
?>