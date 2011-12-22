<?php
define('CONTROLLERKEY','controller');
define('ACTIONKEY','action');
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('TESTING',true);

include('/../config.php');
include('/../classes/Person.php');
include('/../classes/Tag.php');
class BaseControllerTest extends PHPUnit_Framework_TestCase{
	public function correctExtendedController(){
        setUp('test','test');
        $this->assertEquals('Test',BaseController::CurrentController());
	}
	public function testCorrectAction(){
		$tc = new TestController();
        $tc->init();
        $tc->executeAction('test');
        $this->assertArrayHasKey('test',$tc->bag);
	}
    public function testActionParams(){
        global $testquery,$testpost;
        $testquery=array();
        $testpost=array();
        $testquery['test']='value1';
        $testquery['test2']='value2';
        $tc = new TestController();
        $tc->init();
        $tc->executeAction('params');

        $this->assertEquals('value1',$tc->test);
        $this->assertEquals('value2',$tc->test2);
    }
    public function testActionClassParams(){
        global $testquery,$testpost;
        $testquery=array();
        $testpost=array();
        $testpost['userid']='johnsmith';
        $testpost['person_Name']='john';
        $testpost['person_Age']='22';
        $testpost['person_Hcp']='10,7';
        $tc = new TestController();
        $tc->init();
        $tc->executeAction('paramsClassAndParam');
        $this->assertInstanceOf('Person',$tc->person);
        $this->assertEquals('john',$tc->person->Name);
        $this->assertEquals('johnsmith',$tc->userid);
    }
    public function testActionClassDiffNameAndParams(){
            global $testquery,$testpost;
            $testquery=array();
            $testpost=array();
            $testpost['userid']='johnsmith';
            $testpost['user_Name']='john';
            $testpost['user_Age']='22';
            $testpost['user_Hcp']='10,7';
            $tc = new TestController();
            $tc->init();
            $tc->executeAction('paramsClassAndParam2');
            $this->assertInstanceOf('Person',$tc->person);
            $this->assertEquals('john',$tc->person->Name);
            $this->assertEquals('johnsmith',$tc->userid);
        }
}
class TestController extends BaseController{
    public $test,$test2,$person;
    public $userid;

    function __construct(){
        parent::__construct();
        $this->viewpath=VIEWS;
    }
    function params($test,$test2){
        $this->test=$test;
        $this->test2=$test2;
    }
	function test(){
		$this->bag['test']='test';
	}
    function paramsClass(Person $person){
        $this->person=$person;
    }

    function paramsClassAndParam(Person $person,$userid){
            $this->person=$person;
        $this->userid=$userid;
        }
    function paramsClassAndParam2(Person $user,$userid){
            $this->person=$user;
        $this->userid=$userid;
        }
}
function setUp($controller,$action){
    BaseController::setUpRouting($controller,$action);
}