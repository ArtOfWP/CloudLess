<?php
/**
 * User: andreas
 * Date: 2011-12-30
 * Time: 14:32
 */
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
include('/../config.php');
include('/../../lib/ViewEngines/Standard/BIOptions.php');
class OptionsTests extends PHPUnit_Framework_TestCase
{
    function testCreateOption(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $this->assertEquals('key',$option->getKey());
        $this->assertEquals('value',$option->getValue());
        $this->assertEquals('value',$option->getDefaultValue());
    }
    function testCreateOption2(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setValue('value2');
        $this->assertEquals('key',$option->getKey());
        $this->assertEquals('value2',$option->getValue());
        $this->assertEquals('value',$option->getDefaultValue());
    }
    function testCreateOption3(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setValue('value2');
        $option->setType('string');
        $this->assertEquals('string',$option->getType());
    }
    function testCreateNewOptions(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $options->setValue('key','value');
        $options->setValue('key2','value2');
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertEquals('value2',$options->getValue('key2'));
    }
    function testCreateNewSameOptions(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertFalse($options->setValue('key','value2'));
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertNotEquals('value2',$options->getValue('key'));
    }
    function testUpdateOptions(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertTrue($options->updateValue('key','value2'));
        $this->assertNotEquals('value',$options->getValue('key'));
        $this->assertEquals('value2',$options->getValue('key'));
    }
    function testExists(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $options->setValue('key','value');
        $this->assertTrue($options->exists('key'));
    }

    function testDeleteOptions(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $options->setValue('key','value');
        $options->remove('key');
        $this->assertFalse($options->exists('key'));
    }

    function testSetDefaultValue(){
        $bio=new BIOptions('test');
        $options = new Options('test',$bio);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertTrue($options->updateValue('key','value2'));
        $options->reset();
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertNotEquals('value2',$options->getValue('key'));
    }
}