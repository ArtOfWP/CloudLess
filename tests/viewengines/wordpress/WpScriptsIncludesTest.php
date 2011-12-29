<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 17:30
 */

define('LOADALL',true);
define('LOADDATABASE',false);
define('DB_NAME', 'auto_testing');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

include('../../config.php');
include(PACKAGEPATH.'/'.'tests/classes/wp-functions.php');
include(PACKAGEPATH.'lib/viewengine/wordpress/WpFrontIncludes.php');
include(PACKAGEPATH.'lib/viewengine/wordpress/WpScriptIncludes.php');
class WpScriptsIncludesTest extends PHPUnit_Framework_TestCase
{
    function testRegisterScript(){
            $scriptStub = new WpScriptIncludes();
            $si = new ScriptIncludes($scriptStub);
            $si->register($this->getTestFI());
            $this->assertTrue($si->isRegistered('popup'));
        }

        function testUnregisterScript(){
            $scriptStub = new WpScriptIncludes();
            $si = new ScriptIncludes($scriptStub);
            $si->register($this->getTestFI());
            $this->assertTrue($si->deregister($this->getTestFI()->getHandle()));
            $this->assertFalse($si->isRegistered('popup'));
        }
        function testEnqueueScript(){
            $scriptStub = new WpScriptIncludes();
            $si = new ScriptIncludes($scriptStub);
            $si->register($this->getTestFI());
            $si->enqueue('administration',$this->getTestFI());
            $this->assertTrue($si->isEnqueued('popup'));
        }
        function testDeenqueueScript(){
            $scriptStub = new WpScriptIncludes();
            $si = new ScriptIncludes($scriptStub);
            $si->register($this->getTestFI());
            $si->enqueue('administration',$this->getTestFI());
            $this->assertTrue($si->dequeue('administration','popup'));
            $this->assertFalse($si->isEnqueued('popup'));
        }
        function testInitScripts(){
            global $action;
            $scriptStub = new WpScriptIncludes();
            $si = new ScriptIncludes($scriptStub);
            $si->register($this->getTestFI());
            $si->enqueue('administration',$this->getTestFI());
            $si->init();
            $this->assertArrayHasKey('admin_enqueue_scripts',$action);
        }
        function getTestFI(){
            $fi = new FrontInclude();
                    $fi->setHandle('popup');
                    $fi->setSrc('/src/popup.js');

            return $fi;
        }
}