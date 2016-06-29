<?php
namespace tests\unit\core;

use CLMVC\Core\Container;
use PHPUnit_Framework_TestCase;
use stdClass;
use tests\classes\BookDatabase;
use tests\classes\ClassParams;
use tests\classes\ITestDatabase;
use tests\classes\Library;
use tests\classes\Library2;
use tests\classes\Library3;
use tests\classes\ParentClass;
use tests\classes\SubClass;

/**
 * Class ContainerTests
 * @package tests\core
 */
class ContainerTests extends PHPUnit_Framework_TestCase
{
    /**/
    public function testAddObject()
    {
        $c = new Container();
        $c->add('BookDatabase', new stdClass(), 'object');
    }

    public function testGetOject()
    {
        $c = new Container();
        $c->add('BookDatabase', new stdClass(), 'object');
        $obj = $c->fetch('BookDatabase');
        $this->assertNotNull($obj);
    }

    public function testGetClassObject()
    {
        $c = new Container();
        $obj = new BookDatabase();
        $c->add('BookDatabase', $obj, gettype($obj));
        $objTemp = $c->fetch('BookDatabase');
        $this->assertInstanceOf(BookDatabase::class, $objTemp);
        $this->assertSame($obj, $objTemp);
    }

    public function testMakeClassFromClassName()
    {
        $c = new Container();
        $objTemp = $c->make(BookDatabase::class);
        $this->assertInstanceOf(BookDatabase::class, $objTemp);
    }

    public function testMakeClassFromObject()
    {
        $c = new Container();
        $obj = new BookDatabase();
        $c->add('BookDatabase', $obj);
        $objTemp = $c->make('BookDatabase');
        $this->assertInstanceOf(BookDatabase::class, $objTemp);
    }

    public function testGetClassObjectWithDependencyObject()
    {
        $c = new Container();
        $obj = new BookDatabase();
        $c->add(BookDatabase::class, $obj);
        $objTemp = $c->make(Library::class);
        $this->assertInstanceOf(Library::class, $objTemp);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
        $this->assertSame($obj, $objTemp->getDatabase());
    }

    public function testGetClassObjectWithDependencyClass()
    {
        $c = new Container();
        $objTemp = $c->make(Library::class);
        $this->assertInstanceOf(Library::class, $objTemp);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
    }

    public function testGetClassOjbectWithDependencyInterface()
    {
        $c = new Container();
        $c->add(ITestDatabase::class, BookDatabase::class, 'class');
        $objTemp = $c->make(Library2::class);
        $this->assertInstanceOf(Library2::class, $objTemp);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
    }

    /**/
    public function testMakeClassWithVariableParams()
    {
        $c = new Container();
        $c->add(ITestDatabase::class, BookDatabase::class, 'class');
        $objTemp = $c->make(Library3::class, [1 => 'testing']);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
        $this->assertEquals('testing', $objTemp->getSomeOtherParam());
    }

    public function testMakeClassWithVariableParamsAndOptionalSet()
    {
        $c = new Container();
        $c->add(ITestDatabase::class, BookDatabase::class, 'class');
        $objTemp = $c->make(Library3::class, [1 => 'testing', 2 => 'set']);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
        $this->assertEquals('testing', $objTemp->getSomeOtherParam());
        $this->assertEquals('set', $objTemp->getOptionalParam());
    }


    public function testGetClassWithDependencyBasicValues()
    {
        $c = new Container();
        $c->add(ITestDatabase::class, BookDatabase::class, 'class');
        $c->add('connectionString', 'string', gettype('string'));
        $objTemp = $c->make(Library::class);
        $this->assertInstanceOf(Library::class, $objTemp);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
        $this->assertEquals('string', $objTemp->getDatabase()->connectionString);
    }

    public function testShouldSupportVariableSourceOfParameters()
    {
        $c = new Container();
        $c->add(ITestDatabase::class, BookDatabase::class, 'class');
        $c->add('connectionString', 'string', gettype('string'));
        $objTemp = $c->make(Library::class);
        $this->assertInstanceOf(Library::class, $objTemp);
        $this->assertInstanceOf(BookDatabase::class, $objTemp->getDatabase());
        $this->assertEquals('string', $objTemp->getDatabase()->connectionString);
    }

    public function testFetchOrMakeNewObject()
    {
        $c = new Container();
        $bookdb = $c->fetchOrMake(BookDatabase::class);
        $bookdb->connectionString = 'newConnectionString';
        $bookdb = $c->fetchOrMake(BookDatabase::class);
        self::assertEquals('newConnectionString', $bookdb->connectionString);
    }

    public function testFetchOrMakeNewObjectWithParams()
    {
        $c = new Container();
        $bookdb = $c->fetchOrMake(BookDatabase::class, [0 => 'newConnectionString']);
        $bookdb = $c->fetchOrMake(BookDatabase::class);
        self::assertEquals('newConnectionString', $bookdb->connectionString);
    }

    public function testFetchOrMakeNewObjectWithClassParamsOverload()
    {
        $c = new Container();
        $bookdb = $c->fetchOrMake(ClassParams::class, [0 => new ParentClass('test_param')]);
        $bookdb = $c->fetchOrMake(ClassParams::class);
        self::assertEquals('test_param', $bookdb->getClass()->getSomeParam());
    }

    public function testFetchOrMakeNewObjectWithClassParamsOverload2()
    {
        $c = new Container();
        $bookdb = $c->fetchOrMake(ClassParams::class, [1 => new ParentClass('test_param')]);
        $bookdb = $c->fetchOrMake(ClassParams::class);
        self::assertEquals('', $bookdb->getClass()->getSomeParam());
        self::assertEquals('test_param', $bookdb->getClass2()->getSomeParam());
    }

    public function testNoParamsConstructorWithInherit()
    {
        $c = new Container();
        $sub = $c->make(SubClass::class);
        self::assertEquals('someparam', $sub->getSomeParam());
    }
}
