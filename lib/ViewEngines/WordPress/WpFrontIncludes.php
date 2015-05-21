<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Interfaces\IIncludes;

abstract class WpFrontIncludes implements IIncludes
{
    private $includes = array();
    private $queue = array();
    private $dequeue = array();
    private $unincludes = array();
    public function register(FrontInclude $include)
    {
        $this->includes[$include->getHandle()] = $include;

        return $this;
    }

    public function deregister($handle)
    {
        if (isset($this->includes[$handle])) {
            unset($this->includes[$handle]);
        }
        $this->unincludes[] = $handle;

        return $this;
    }

    public function enqueue($location, $handle)
    {
        if (!isset($this->queue[$location]) || empty($this->queue[$location])) {
            $this->queue[$location] = array();
        }
        if (isset($this->includes[$handle])) {
            $this->queue[$location][$handle] = $this->includes[$handle];
        }

        return $this;
    }

    public function dequeue($location, $handle)
    {
        if (isset($this->queue[$location][$handle])) {
            unset($this->queue[$location][$handle]);
        }
        $this->dequeue[$location][] = $handle;

        return $this;
    }

    public function isRegistered($handle)
    {
        return isset($this->includes[$handle]) && !empty($this->includes[$handle]);
    }

    public function isEnqueued($handle)
    {
        foreach ($this->queue as $includes) {
            foreach ($includes as $qHandle => $include) {
                if ($handle == $qHandle) {
                    return true;
                }
            }
        }

        return false;
    }

    public function init()
    {
        add_action('init', array($this, 'registerIncludes'));
        add_action('login_enqueue_scripts', array($this, 'loginEnqueueIncludes'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueIncludes'));
        add_action('wp_enqueue_scripts', array($this, 'wpEnqueueIncludes'));

        return true;
    }

    public function registerIncludes()
    {
        /**
         * @var FrontInclude
         */
        foreach ($this->includes as $include) {
            $this->registerInclude($include);
        }
        foreach ($this->unincludes as $include) {
            $this->deregisterInclude($include);
        }
    }
    public function loginEnqueueIncludes()
    {
        $this->enqueueLocation('login');
    }
    public function adminEnqueueIncludes()
    {
        $this->enqueueLocation('administration');
    }
    public function wpEnqueueIncludes()
    {
        $this->enqueueLocation('frontend');
    }

    /**
     * @param string $location
     */
    private function enqueueLocation($location)
    {
        /*
        * @var $include FrontInclude
        */
        if (isset($this->queue[$location])) {
            foreach ($this->queue[$location] as $include) {
                if ($include) {
                    $this->enqueueInclude($include);
                }
            }
        }
        if (isset($this->dequeue[$location])) {
            foreach ($this->dequeue[$location] as $include) {
                if ($include) {
                    $this->dequeueInclude($include);
                }
            }
        }
    }

    /**
     * @param string $handle
     * @return FrontInclude
     */
    public function getRegistered($handle) {
        return $this->includes[$handle];
    }

    /**
     * @return FrontInclude[]
     */
    public function getAllRegistered() {
        return $this->includes;
    }

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location)
    {
        return $this->queue[$location];
    }
    abstract public function enqueueInclude(FrontInclude $include);
    abstract public function registerInclude(FrontInclude $include);
    abstract public function dequeueInclude($includeHandle);
    abstract public function deregisterInclude($includeHandle);
}
