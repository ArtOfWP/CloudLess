<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 16:14
 */
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('/../config.php');
include('/../classes/BookDatabase.php');
include('/../classes/Library.php');
class ContainerTests extends PHPUnit_Framework_TestCase
{
    public function testAddObject(){
        $c = new Container();
        $c->add('BookDatabase', new stdClass(),'object');
    }
    public function testGetOject(){
        $c= new Container();
        $c->add('BookDatabase', new stdClass(),'object');
        $obj = $c->fetch('BookDatabase');
        $this->assertNotNull($obj);
    }
    public function testGetClassObject(){
        $c= new Container();
        $obj=new BookDatabase();
        $c->add('BookDatabase', $obj,gettype($obj));
        $objTemp = $c->fetch('BookDatabase');
        $this->assertInstanceOf('BookDatabase',$objTemp);
        $this->assertSame($obj,$objTemp);
    }
    public function testMakeClassFromClassName(){
        $c= new Container();
        $c->add('BookDatabase', 'BookDatabase','class');
        $objTemp = $c->make('BookDatabase');
        $this->assertInstanceOf('BookDatabase',$objTemp);
    }
    public function testMakeClassFromObject(){
        $c= new Container();
        $obj=new BookDatabase();
        $c->add('BookDatabase', $obj);
        $objTemp = $c->make('BookDatabase');
        $this->assertInstanceOf('BookDatabase',$objTemp);
    }
    public function testGetClassObjectWithDependencyObject(){
        $c = new Container();
        $obj=new BookDatabase();
        $c->add('BookDatabase', $obj);
        $c->add('Library','Library','class');
        $objTemp = $c->make('Library');
        $this->assertInstanceOf('Library',$objTemp);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertInstanceOf('BookDatabase',$objTemp->getDatabase());
        $this->assertSame($obj,$objTemp->getDatabase());
    }
    public function testGetClassObjectWithDependencyClass(){
            $c = new Container();
            $c->add('BookDatabase','BookDatabase','class');
            $c->add('Library','Library','class');
            $objTemp = $c->make('Library');
            $this->assertInstanceOf('Library',$objTemp);
            /** @noinspection PhpUndefinedMethodInspection */
            $this->assertInstanceOf('BookDatabase',$objTemp->getDatabase());
    }
    public function testGetClassOjbectWithDependencyInterface(){
        $c = new Container();
        $c->add('ITestDatabase','BookDatabase','class');
        $c->add('Library','Library2','class');
        $objTemp = $c->make('Library');
        $this->assertInstanceOf('Library2',$objTemp);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertInstanceOf('BookDatabase',$objTemp->getDatabase());
    }
    public function testGetClassOjbectWithDependencyInterface2(){
            $c = new Container();
            $c->add('idatabase','BookDatabase','class');
            $c->add('Library','Library2','class');
            $objTemp = $c->make('Library');
            $this->assertInstanceOf('Library2',$objTemp);
            /** @noinspection PhpUndefinedMethodInspection */
            $this->assertInstanceOf('BookDatabase',$objTemp->getDatabase());
        }
    public function testGetClassWithDependencyBasicValues(){
        $c = new Container();
        $c->add('ITestDatabase','BookDatabase','class');
        $c->add('connectionString','string',gettype('string'));
        $c->add('Library','Library2','class');
        $objTemp = $c->make('Library');
        $this->assertInstanceOf('Library2',$objTemp);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertInstanceOf('BookDatabase',$objTemp->getDatabase());
        $this->assertEquals('string',$objTemp->getDatabase()->connectionString);
    }
}
