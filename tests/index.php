<?php
define('PACKAGEPATH',dirname(__FILE__).'../../');
//require_once(PACKAGEPATH.'install.php');
require_once(PACKAGEPATH.'lib/testing/TestRunner.php');
class AoiSoraTestSuite extends TestRunner{
	function __construct(){
		parent::TestRunner(dirname(__FILE__).'/');
	}
}
$testsuite=new AoiSoraTestSuite();
$testsuite->runTests();
?>