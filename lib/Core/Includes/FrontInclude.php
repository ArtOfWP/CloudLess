<?php

namespace CLMVC\Core\Includes;

/**
 * Class FrontInclude
 * Stylesheet or JavaScript file that should be registered and loaded.
 */
class FrontInclude
{
    private $handle, $src, $dependency = array(), $version = '', $inFooter = false;

    /**
     * @param string   $handle     name of the resource
     * @param string   $src        path to resource
     * @param string[] $dependency list of short names(handles) that the include depends on
     * @param string   $version    version number for the resource
     * @param bool     $inFooter
     */
    public function __construct($handle = '', $src = '', $dependency = array(), $version = '', $inFooter = false)
    {
        $this->handle = $handle;
        $this->src = $src;
        $this->dependency = $dependency;
        $this->version = $version;
        $this->inFooter = $inFooter;
    }

    /**
     * Set the name for the resource.
     *
     * @param $handle
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    /**
     * Get the name for the resource.
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Set resource dependencies.
     *
     * @param string[] $dependency
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * Get resource dependencies.
     *
     * @return array
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * If resource should be loaded in footer.
     *
     * @param $inFooter
     */
    public function setInFooter($inFooter)
    {
        $this->inFooter = $inFooter;
    }

    /**
     * @return bool
     */
    public function loadInFooter()
    {
        return $this->inFooter;
    }

    /**
     * Set the path to the resource.
     *
     * @param $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
    }

    /**
     * Get the path to the resource.
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Set the version number.
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Register the include as a script.
     */
    public function register()
    {
        $si = new ScriptIncludes();
        $si->register($this);
    }

    /**
     * Enqueue the resource to be loaded.
     *
     * @param string $location
     */
    public function enqueue($location)
    {
        $si = new ScriptIncludes();
        $si->enqueue($location, $this->getHandle());
    }
}
