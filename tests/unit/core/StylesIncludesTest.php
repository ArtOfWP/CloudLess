<?php
namespace tests\unit\core;

use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Core\Includes\StyleIncludes;
use CLMVC\Interfaces\IIncludes;
use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Class StylesIncludesTest
 * @package tests\unit\core
 */
class StylesIncludesTest extends PHPUnit_Framework_TestCase
{
    function testRegisterStyle()
    {
        $styleStub = Mockery::mock(IIncludes::class);
        $styleStub->shouldReceive('init')->andReturn(true);
        $styleStub->shouldReceive('register');
        $styleStub->shouldReceive('isRegistered')->andReturn(true);
        /**  @var IIncludes $styleStub */
        $si = new StyleIncludes($styleStub);
        $si->register($this->getTestFI());
        $this->assertTrue($si->isRegistered('popup'));
    }

    function getTestFI()
    {
        $fi = new FrontInclude();
        $fi->setHandle('popup');
        $fi->setSrc('/src/popup.js');

        return $fi;
    }

    function testUnregisterStyle()
    {
        $styleStub = Mockery::mock(IIncludes::class);
        $styleStub->shouldReceive('init')->andReturn(true);
        $styleStub->shouldReceive('deregister')->andReturn($styleStub);
        $styleStub->shouldReceive('register')->andReturn($styleStub);
        $styleStub->shouldReceive('isRegistered')->andReturn(false);
        /**  @var IIncludes $styleStub */
        $si = new StyleIncludes($styleStub);
        $si->register($this->getTestFI());
        $this->assertTrue($si->deregister($this->getTestFI()) instanceof IIncludes);
        $this->assertFalse($si->isRegistered('popup'));
    }

    function testEnqueueStyle()
    {
        $styleStub = Mockery::mock(IIncludes::class);
        $styleStub->shouldReceive('init')->andReturn(true);
        $styleStub->shouldReceive('register');
        $styleStub->shouldReceive('isRegistered')->andReturn(true);
        $styleStub->shouldReceive('isEnqueued')->andReturn(true);
        $styleStub->shouldReceive('enqueue')->andReturn($styleStub);


        /**  @var IIncludes $styleStub */
        $si = new StyleIncludes($styleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->isEnqueued('popup'));
    }

    function testDeenqueueStyle()
    {
        $styleStub = Mockery::mock(IIncludes::class);
        $styleStub->shouldReceive('init')->andReturn(true);
        $styleStub->shouldReceive('register');
        $styleStub->shouldReceive('isRegistered')->andReturn(true);
        $styleStub->shouldReceive('isEnqueued')->andReturn(false);
        $styleStub->shouldReceive('dequeue')->andReturn($styleStub);
        $styleStub->shouldReceive('enqueue')->andReturn($styleStub);

        /**  @var IIncludes $styleStub */
        $si = new StyleIncludes($styleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->dequeue('administration', 'popup') instanceof IIncludes);
        $this->assertFalse($si->isEnqueued('popup'));
    }

    function testInitStyles()
    {
        $styleStub = Mockery::mock(IIncludes::class);
        $styleStub->shouldReceive('init')->andReturn(true);
        $styleStub->shouldReceive('register');
        $styleStub->shouldReceive('isRegistered')->andReturn(true);
        $styleStub->shouldReceive('enqueue')->andReturn($styleStub);

        /**  @var IIncludes $styleStub */
        $si = new StyleIncludes($styleStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->init());
    }
}
