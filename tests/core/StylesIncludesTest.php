<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 12:12
 */
define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('/../config.php');

class StylesIncludesTest extends PHPUnit_Framework_TestCase
{
    function testRegisterStyle(){
        /**  @var IIncludes $StyleStub */
        $StyleStub = $this->getMock('IIncludes');
        $si = new StyleIncludes($StyleStub);
        $si->register($this->getTestFI());
        $StyleStub->expects($this->any())->method('isRegistered')->will($this->returnValue(true));
        $this->assertTrue($si->isRegistered('popup'));
    }

    function testUnregisterStyle(){
        /**  @var IIncludes $StyleStub */
        $StyleStub = $this->getMock('IIncludes');
        $si = new StyleIncludes($StyleStub);
        $si->register($this->getTestFI());
        $StyleStub->expects($this->any())->method('isRegistered')->will($this->returnValue(false));
        $this->assertTrue($si->deregister($this->getTestFI()));
        $this->assertFalse($si->isRegistered('popup'));
    }
    function testEnqueueStyle(){
        /**  @var IIncludes $StyleStub */
        $StyleStub = $this->getMock('IIncludes');
        $si = new StyleIncludes($StyleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration',$this->getTestFI());
        $StyleStub->expects($this->any())->method('isEnqueued')->will($this->returnValue(true));
        $this->assertTrue($si->isEnqueued('popup'));
    }
    function testDeenqueueStyle(){
        /**  @var IIncludes $StyleStub */
        $StyleStub = $this->getMock('IIncludes');
        $si = new StyleIncludes($StyleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration',$this->getTestFI());
        $StyleStub->expects($this->any())->method('isEnqueued')->will($this->returnValue(false));
        $this->assertTrue($si->dequeue('administration','popup'));
        $this->assertFalse($si->isEnqueued('popup'));
    }
    function testInitStyles(){
        /**  @var IIncludes $StyleStub */
        $StyleStub = $this->getMock('IIncludes');
        $si = new StyleIncludes($StyleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration',$this->getTestFI());
        $StyleStub->expects($this->any())->method('init')->will($this->returnValue(true));
        $this->assertTrue($si->init());
    }
    function getTestFI(){
        $fi = new FrontInclude();
                $fi->setHandle('popup');
                $fi->setSrc('/src/popup.js');

        return $fi;
    }
}
