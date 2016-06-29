<?php

namespace CLMVC\Core\Includes;

use CLMVC\Core\Container;
use CLMVC\Events\Hook;
use CLMVC\Interfaces\IIncludes;

/**
 * Class StyleIncludes.
 */
class StyleIncludes implements IIncludes
{
    public static $instance;

    /**
     * @var IIncludes
     */
    private $styleInclude;

    /**
     * Instantiate a Container.
     *
     * @static
     *
     * @return StyleIncludes
     */
    public static function instance()
    {
        if (!isset(self::$instance) && empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Massregister includes
     * @param array<FrontInclude> $includes
     */
    public static function registerAll($includes) {
        $handler = self::instance();
        foreach($includes as $include) {
            $handler->register($include);
        }
    }

    /**
     * Inject a style handler for includes.
     *
     * @param IIncludes $iStyleInclude
     */
    public function __construct(IIncludes $iStyleInclude = null)
    {
        if ($iStyleInclude) {
            $this->styleInclude = $iStyleInclude;
        } else {
            $this->styleInclude = Container::instance()->fetch('CLMVC\\Interfaces\\IStyleInclude');
        }
        $this->styleInclude->init();
        Hook::register('stylesheets-register', array($this, 'registerIncludes'));
    }

    /**
     * Register a include.
     *
     * @param FrontInclude $include
     *
     * @return ScriptIncludes
     */
    public function register(FrontInclude $include)
    {
        $this->styleInclude->register($include);

        return $this->styleInclude;
    }

    /**
     * Deregister a resource using its handle.
     *
     * @param string $handle
     *
     * @return ScriptIncludes
     */
    public function deregister($handle)
    {
        $this->styleInclude->deregister($handle);

        return $this->styleInclude;
    }

    /**
     * Enqueue a resource to be loaded.
     *
     * @param string $location where it should be loaded
     * @param string $handle
     *
     * @return ScriptIncludes
     */
    public function enqueue($location, $handle)
    {
        $this->styleInclude->enqueue($location, $handle);

        return $this->styleInclude;
    }

    /**
     * Remove resource from queue.
     *
     * @param string $location
     * @param string $handle
     *
     * @return ScriptIncludes
     */
    public function dequeue($location, $handle)
    {
        $this->styleInclude->dequeue($location, $handle);

        return $this->styleInclude;
    }

    /**
     * Check if resource is queued.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function isEnqueued($handle)
    {
        return $this->styleInclude->isEnqueued($handle);
    }

    /**
     * Check if resource is registered.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function isRegistered($handle)
    {
        return $this->styleInclude->isRegistered($handle);
    }

    /**
     * Initiate the include handler.
     *
     * @return bool
     */
    public function init()
    {
        return $this->styleInclude->init();
    }

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location)
    {
        return $this->styleInclude->getEnqueued($location);
    }

    public function registerIncludes()
    {
        $this->styleInclude->registerIncludes();
    }

    public function enqueueIncludes($location,$styles)
    {
        foreach($styles as $style)
            $this->styleInclude->enqueue($location, $style);
    }


    /**
     * @param string $handle
     * @return FrontInclude
     */
    public function getRegistered($handle)
    {
        return $this->styleInclude->getRegistered($handle);
    }

    /**
     * @return FrontInclude[]
     */
    public function getAllRegistered()
    {
        return $this->styleInclude->getAllRegistered();
    }
}
