<?php
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('/../config.php');
include('/../classes/Person.php');
include('/../classes/Tag.php');
class CrudControllerTest extends PHPUnit_Framework_TestCase{
	private static $db;
	public function __construct(){
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;		
	}
	public static function setUpBeforeClass(){
		self::$db=new MySqlDatabase();
		global $db;
		$db=self::$db;
		$p=new Person();
		self::$db->createTable($p);	
	}
	public function testCorrectExtendedController(){
		$tc = new PersonController(false);
		$tc->init();
		$this->assertEquals('Person',$tc->currentController());
	}

	public function testCreate(){
		$tc = new PersonController(false);
		$tc->init();
		$_POST['Name']='Robert Johnson';
		$_POST['Introduction']='Fatguy';
		$tc->create();
		$stmt=self::$db->db->query("select * from person where name='Robert Johnson'");
		$rows=$stmt->fetchAll();
		$this->assertEquals(1,sizeof($rows));
	}	
	public function setUpAfterClass(){
		$p=new Person();
		self::$db->dropTable($p);
	}	
}
class PersonController extends CrudController{
	function currentController(){
		return $this->controller;
	}
	function test(){
		$this->bag['test']='test';
	}
}