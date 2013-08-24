<?php
define('PACKAGEPATH',dirname(__FILE__).'../../');
class AoiSoraTestSuite extends TestRunner{
	function __construct(){
		parent::TestRunner(dirname(__FILE__).'/');
	}
}
$testsuite=new AoiSoraTestSuite();
$testsuite->runTests();
