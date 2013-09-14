<?php
/**
 * User: andreas
 * Date: 2011-12-30
 * Time: 20:28
 */
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('../../config.php');
include(PACKAGEPATH.'/'.'tests/classes/wp-functions.php');
include(PACKAGEPATH . 'lib/ViewEngines/WordPress/WpOptions.php');

class WpOptionsTests extends PHPUnit_Framework_TestCase
{
    function testCreateNewOptions(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->setValue('key','value');
        $options->setValue('key2','value2');
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertEquals('value2',$options->getValue('key2'));
    }
    function testCreateNewSameOptions(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertFalse($options->setValue('key','value2'));
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertNotEquals('value2',$options->getValue('key'));
    }
    function testAddNewOptions(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setValue('value');
        $option->setType('string');
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->add($option);
        $this->assertEquals('value',$options->get('key')->getValue());
    }
    function testUpdateOptions(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertTrue($options->updateValue('key','value2'));
        $this->assertNotEquals('value',$options->getValue('key'));
        $this->assertEquals('value2',$options->getValue('key'));
    }
    function testExists(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->setValue('key','value');
        $this->assertTrue($options->exists('key'));
    }
    function testDeleteOptions(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->setValue('key','value');
        $options->remove('key');
        $this->assertFalse($options->exists('key'));
    }
    function testSetDefaultValue(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $this->assertTrue($options->setValue('key','value'));
        $this->assertTrue($options->updateValue('key','value2'));
        $options->reset();
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertNotEquals('value2',$options->getValue('key'));
    }
    function testSaveValues(){
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->setValue('key','value');
        $options->setValue('key2','value2');
        $options->save();
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->init();
        $this->assertEquals('value',$options->getValue('key'));
        $this->assertEquals('value2',$options->getValue('key2'));
    }
    function testAddSaveAddValues(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setType('string');
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->add($option);
        $options->updateValue('key','value2');
        $options->save();
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setType('string');
        $options->add($option);
        $options->init();
        $this->assertEquals('value2',$options->get('key')->getValue());
        $this->assertEquals('value',$options->get('key')->getDefaultValue());
    }
    function testAddSaveInitAddValues(){
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setType('string');
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $options->add($option);
        $options->updateValue('key','value2');
        $options->save();
        $wpoptions = new WpOptions('test');
        $options = new Options('test',$wpoptions);
        $option= new Option();
        $option->setKey('key');
        $option->setDefaultValue('value');
        $option->setType('string');
        $options->init();
        $options->add($option);
        $this->assertEquals('value2',$options->get('key')->getValue());
        $this->assertEquals('value',$options->get('key')->getDefaultValue());
    }
}