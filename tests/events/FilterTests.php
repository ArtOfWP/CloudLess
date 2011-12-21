<?php
define('LOADALL',false);
define('LOADDATABASE',false);
include('/../config.php');
include(PACKAGEPATH.'lib/helpers/ArraysHelper.php');
include(PACKAGEPATH.'lib/events/Filter.php');
global $customStuff;
$customStuff=array();
class FilterTest extends PHPUnit_Framework_TestCase{
	public function __construct(){
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;		
	}
    public function setUp(){
        Filter::$FilterSections=array();
    }
	public function testregister(){
		Filter::register('test_filter','testFilter');
		$this->assertTrue(Filter::isRegistered('test_filter'));
	}
	public function testrunVarFunction(){
		Filter::register('test_filter','testFilter');
		$testvalue='test';
		$temp=Filter::run('test_filter',$testvalue);
		$this->assertEquals('notest',$temp);
	}
	public function testrunArrayFunction(){
		Filter::register('test_filter2','testArrayFilter');
		$testvalues=array('testvalue1','testvalue2');
		$temp=Filter::run('test_filter2',array($testvalues));
		$this->assertEquals(3,sizeof($temp));
		$this->assertEquals('testvalue3',$temp[2]);
	}
    public function testrunVarClassStaticMethod(){
    		Filter::register('test_filter',array('TestFilterClass','testFilter'));
    		$testvalue='test';
    		$temp=Filter::run('test_filter',$testvalue);
    		$this->assertEquals('notest',$temp);
    	}
    	public function testrunArrayClassStaticMethod(){
    		Filter::register('test_filter2',array('TestFilterClass','testArrayFilter'));
    		$testvalues=array('testvalue1','testvalue2');
    		$temp=Filter::run('test_filter2',array($testvalues));
    		$this->assertEquals(3,sizeof($temp));
    		$this->assertEquals('testvalue3',$temp[2]);
    	}
	public function testRegisterCustomHandler(){
		Filter::registerHandler('test_filter_handler','testFilterHandler2');
		$this->assertTrue(Filter::hasHandler('test_filter_handler'));
	}
	public function testRunCustomHandler(){
		Filter::registerHandler('test_filter_handler2','testFilterHandler');
		$this->assertTrue(Filter::hasHandler('test_filter_handler2'));
		Filter::register('test_filter_handler2','test_filter_handler2');
		global $customStuff;
		$this->assertTrue(isset($customStuff['test_filter_handler2']));
	}	
}
function testFilterHandler2($section,$func){}
function testFilterHandler($section,$func){
	global $customStuff;	
	$customStuff[$section]=$func;
}
function testFilter($testvalue){
	$testvalue='notest';
	return $testvalue;
}
function testArrayFilter($testvalues=array()){
	$testvalues[]='testvalue3';
	return $testvalues;
}
class TestFilterClass{
    static function testFilter($testvalue){
    	$testvalue='notest';
    	return $testvalue;
    }
    static function testArrayFilter($testvalues=array()){
    	$testvalues[]='testvalue3';
    	return $testvalues;
    }
}