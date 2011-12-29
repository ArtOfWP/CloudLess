<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 16:57
 */
class FrontInclude
{
    private $handle, $src, $dependency = array(), $version = false, $inFooter = false;
    function __construct($handle=false, $src=false, $dependency = array(), $version = false, $inFooter = false){
        $this->handle=$handle;
        $this->src=$src;
        $this->dependency=$dependency;
        $this->version=$version;
        $this->inFooter=$inFooter;
    }
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency()
    {
        return $this->dependency;
    }

    public function setInFooter($inFooter)
    {
        $this->inFooter = $inFooter;
    }

    public function getInFooter()
    {
        return $this->inFooter;
    }

    public function setSrc($src)
    {
        $this->src = $src;
    }

    public function getSrc()
    {
        return $this->src;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }
    public function register(){
        $si = new ScriptIncludes();
        $si->register($this);
    }
    public function enqueue($location){
        $si = new ScriptIncludes();
        $si->enqueue($location,$this);
    }
}
