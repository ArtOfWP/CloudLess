<?php
define('LOADALL',false);
define('LOADDATABASE',false);
include('/../config.php');
include(PACKAGEPATH.'lib/helpers/FilterHelper.php');
global $customStuff;
$customStuff=array();
class FilterHelperTest extends PHPUnit_Framework_TestCase{
	public function __construct(){
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;		
	}
	public function testRegisterFilter(){
		FilterHelper::registerFilter('test_filter','testFilter');
		$this->assertTrue(FilterHelper::isRegistered('test_filter'));
	}
	public function testRunFilterVar(){
		FilterHelper::registerFilter('test_filter','testFilter');
		$testvalue='test';
		$temp=FilterHelper::runFilter('test_filter',$testvalue);
		$this->assertEquals('notest',$temp);
	}
	public function testRunFilterArray(){
		FilterHelper::registerFilter('test_filter2','testArrayFilter');
		$testvalues=array('testvalue1','testvalue2');
		$temp=FilterHelper::runFilter('test_filter2',array($testvalues));
		$this->assertEquals(3,sizeof($temp));
		$this->assertEquals('testvalue3',$temp[2]);
	}
	public function testRegisterCustomHandler(){
		FilterHelper::registerCustomHandler('test_filter_handler','testFilterHandler2');
		$this->assertTrue(FilterHelper::hasCustomHandler('test_filter_handler'));
	}
	public function testRunCustomHandler(){
		FilterHelper::registerCustomHandler('test_filter_handler2','testFilterHandler');
		$this->assertTrue(FilterHelper::hasCustomHandler('test_filter_handler2'));
		FilterHelper::registerFilter('test_filter_handler2','test_filter_handler2');
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