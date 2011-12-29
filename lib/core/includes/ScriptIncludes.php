<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 12:06
 */
class ScriptIncludes implements IIncludes
{
    private $scriptInclude;
    function __construct(IIncludes $iScriptInclude=NULL){
        if($iScriptInclude)
            $this->scriptInclude=$iScriptInclude;
        else
            $this->scriptInclude=Container::instance()->fetch('IScriptInclude');
        $this->scriptInclude->init();
    }
    /**
     * @param string $handle the id for the script
     * @param string $src the url path to the script
     * @param array $deps other includes that this include depends on
     * @param bool $ver version number of the script
     * @param bool $in_footer
     */
    public function register(FrontInclude $include)
    {
        $this->scriptInclude->register($include);
    }

    /**
     * @param $handle string
     * @return bool
     */
    function deregister($handle)
    {
        return $this->scriptInclude->deregister($handle);
    }

    function enqueue($location, FrontInclude $include)
     {
         $this->scriptInclude->enqueue($location,$include);
     }
/**
 * @param $location string
 * @param $handle string
 * @return bool
 */
    function dequeue($location, $handle)
    {
        return $this->scriptInclude->dequeue($location,$handle);
    }
    /**
     * @param $handle string
     * @return bool
     */
    function isEnqueued($handle)
    {
        return $this->scriptInclude->isEnqueued($handle);
    }
    /**
     * @param $handle string
     * @return bool
     */
    public function isRegistered($handle)
    {
        return $this->scriptInclude->isRegistered($handle);
    }
    /**
     * @return bool
     */
    function init()
    {
        return $this->scriptInclude->init();
    }
}
