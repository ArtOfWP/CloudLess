<?php
namespace tests\unit\core;

use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Core\Includes\ScriptIncludes;
use CLMVC\Interfaces\IIncludes;
use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Class ScriptsIncludesTest
 */
class ScriptsIncludesTest extends PHPUnit_Framework_TestCase
{
    function testRegisterScript()
    {
        $scriptStub = Mockery::mock(IIncludes::class);
        $scriptStub->shouldReceive('init');
        $scriptStub->shouldReceive('register');
        $scriptStub->shouldReceive('isRegistered')->andReturn(true);
        /**  @var IIncludes $scriptStub */
        $si = new ScriptIncludes($scriptStub);
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

    function testUnregisterScript()
    {
        $scriptStub = Mockery::mock(IIncludes::class);
        $scriptStub->shouldReceive('init');
        $scriptStub->shouldReceive('register');
        $scriptStub->shouldReceive('isRegistered')->andReturn(false);
        $scriptStub->shouldReceive('deregister')->andReturn(true);
        /**  @var IIncludes $scriptStub */

        $si = new ScriptIncludes($scriptStub);
        $si->register($this->getTestFI());
        $this->assertTrue($si->deregister($this->getTestFI()) instanceof IIncludes);
        $this->assertFalse($si->isRegistered('popup'));
    }

    function testEnqueueScript()
    {
        $scriptStub = Mockery::mock(IIncludes::class);
        $scriptStub->shouldReceive('init');
        $scriptStub->shouldReceive('register');
        $scriptStub->shouldReceive('isRegistered')->andReturn(true);
        $scriptStub->shouldReceive('isEnqueued')->andReturn(true);
        $scriptStub->shouldReceive('enqueue')->andReturn($scriptStub);

        /**  @var IIncludes $scriptStub */
        $si = new ScriptIncludes($scriptStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->isEnqueued('popup'));
    }

    function testDeenqueueScript()
    {
        $scriptStub = Mockery::mock(IIncludes::class);
        $scriptStub->shouldReceive('init');
        $scriptStub->shouldReceive('register');
        $scriptStub->shouldReceive('isRegistered')->andReturn(true);
        $scriptStub->shouldReceive('isEnqueued')->andReturn(false);
        $scriptStub->shouldReceive('enqueue')->andReturn($scriptStub);
        $scriptStub->shouldReceive('dequeue')->andReturn($scriptStub);

        /**  @var IIncludes $scriptStub */
        $si = new ScriptIncludes($scriptStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->dequeue('administration', 'popup') instanceof IIncludes);
        $this->assertFalse($si->isEnqueued('popup'));
    }

    function testInitScripts()
    {
        $scriptStub = Mockery::mock(IIncludes::class);
        $scriptStub->shouldReceive('init')->andReturn(true);
        $scriptStub->shouldReceive('register');
        $scriptStub->shouldReceive('isRegistered')->andReturn(true);
        $scriptStub->shouldReceive('enqueue')->andReturn($scriptStub);

        /**  @var IIncludes $scriptStub */
        $si = new ScriptIncludes($scriptStub);
        $si->register($this->getTestFI());
        $si->enqueue('administration', $this->getTestFI());
        $this->assertTrue($si->init());
    }
}
