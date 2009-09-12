<?php
class Assert{
	private $pass;
	private $testitem;
	private $message;
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
		
	}
	public function Contains($value){
		
	}
	public function DoNotContain($value){
		
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