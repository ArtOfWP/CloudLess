<?php
define('CONTROLLERKEY','controller');
define('ACTIONKEY','action');
class BaseControllerTests{
	public function correctExtendedController(){
		$tc = new TestController(false);
		return Assert::That($tc->currentController())->Equals('Test')->Message('Wrong controller name returned');
	}
	public function correctAction(){
		global $testquery;
		$testquery=array();
		$testquery[ACTIONKEY]='test';
		$tc = new TestController();
		return Assert::That($tc->bag)->KeyExist('test')->Message('Action was not executed');
	}
	public function executeActionDoesNotContain(){
		global $testquery;
		$testquery=array();
		$testquery[ACTIONKEY]='test';
		$tc = new TestController();
		return Assert::That($tc->bag)->KeyDoesNotExist('test2')->Message('Key did exist');
	}
}
class TestController extends BaseController{
	function currentController(){
		return $this->controller;
	}
	function test(){
		$this->bag['test']='test';
	}
}
?>