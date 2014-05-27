<?php
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('/../../config.php');
include('/../../classes/Person.php');
include('/../../classes/Tag.php');
class ActiveRecordBaseTests extends PHPUnit_Framework_TestCase{
	private static $db,$dbPure;
	public function __construct(){
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;		
	}
	public static function setUpBeforeClass(){
		self::$db=new MySqlDatabase();
		global $db;
		$db=self::$db;
		self::$dbPure= new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
	}
	public function testCreateTable(){
		$p=new Person();
		self::$db->createTable($p);
		$stmt=self::$dbPure->query('show tables');
		$rows=$stmt->fetchAll();
		$this->assertTrue(array_key_has_value('Tables_in_auto_testing','person',$rows[0]));
	}
	public function testCreateRelations(){
		self::$db->createStoredRelations();
		$stmt=self::$dbPure->query('show tables');
		$rows=$stmt->fetchAll();
		$this->assertTrue(array_key_has_value('Tables_in_auto_testing','taggedpersons',$rows[1]));
	}
	public function testCreateTagTable(){
		$t=new Tag();
		self::$db->createTable($t);
		$stmt=self::$dbPure->query('show tables');
		$rows=$stmt->fetchAll();
		$this->assertTrue(array_key_has_value('Tables_in_auto_testing','tag',$rows[1]));
	}
	public function testInsertRecord(){
		$p=new Person();
		$p->Name='Robert Johnson';
		$p->create();
		$stmt=self::$dbPure->query("select * from person where name='Robert Johnson'");
		$rows=$stmt->fetchAll();
		$this->assertEquals(1,sizeof($rows));
	}
	public function testUpdateRecord(){
		$stmt=self::$dbPure->query("select * from person where name='Robert Johnson'");
		$rows=$stmt->fetchAll();
		$row=$rows[0];
		$p=new Person();
		$p->Id=$row['id'];
		$p->Name=$row['name'];
		$p->Introduction='A fat guy with huge boobs that gets shot in the head';
		$p->update();
		$stmt=self::$dbPure->query("select * from person where name='Robert Johnson'");
		$rows=$stmt->fetchAll();
		$row=$rows[0];
		$this->assertEquals('A fat guy with huge boobs that gets shot in the head',$row['introduction']);
	}
	public function testSaveRecord(){
		$p=new Person();
		$p->Name='Tyler Durden';
		$p->save();
		$stmt=self::$dbPure->query("select * from person where name='Tyler Durden'");
		$rows=$stmt->fetchAll();
		$this->assertEquals(1,sizeof($rows));
	}
	public function testSaveTaggedPerson(){
		$t=new Tag();
		$t->Name='Edward Norton';
		$t->Slug='edward-norton';
		$p=new Person();
		$p->Name='The Narrator';
		$p->addTags($t);
		$p->save();
		$stmt=self::$dbPure->query("select * from person where name='The Narrator'");
		$rows=$stmt->fetchAll();
		$id=$rows[0]['id'];
		$stmt=self::$dbPure->query("select * from taggedpersons where person_id=$id");
		$rows=$stmt->fetchAll();

		$this->assertEquals(1,(int)$rows[0][0]);	
		$this->assertEquals(3,(int)$rows[0][1]);			
	}	
	public function testDeleteRecord(){
		$stmt=self::$dbPure->query("select * from person where name='Tyler Durden'");
		$rows=$stmt->fetchAll();
		$row=$rows[0];
		$p=new Person();
		$p->Id=$row['id'];
		$p->delete();
		$stmt=self::$dbPure->query("select * from person where name='Tyler Durden'");
		$rows=$stmt->fetchAll();
		$this->assertEquals(0,sizeof($rows));
	}	
	public function testRemoveTable(){
		$p=new Person();
		self::$db->dropTable($p);
		$t=new Tag();
		self::$db->dropTable($t);		
		$stmt=self::$dbPure->query('show tables');
		$rows=$stmt->fetchAll();
		$this->assertFalse(array_key_has_value('Tables_in_auto_testing','person',$rows[0]));
	}
	public function testRemoaveRelations(){
		self::$db->dropStoredRelations();	
		$stmt=self::$dbPure->query('show tables');
		$rows=$stmt->fetchAll();
		$this->assertFalse(array_key_has_value('Tables_in_auto_testing','taggedpersons',$rows[0]));		
	}
}