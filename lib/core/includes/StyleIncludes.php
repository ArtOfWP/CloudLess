<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 12:06
 */
class StyleIncludes implements IIncludes
{
    private $styleInclude;
    function __construct(IIncludes $iStyleInclude=NULL){
        if($iStyleInclude)
            $this->styleInclude=$iStyleInclude;
        else
            $this->styleInclude=Container::instance()->fetch('IStyleInclude');
        $this->styleInclude->init();
    }
    /**
     * @param string $handle the id for the Style
     * @param string $src the url path to the Style
     * @param array $deps other includes that this include depends on
     * @param bool $ver version number of the Style
     * @param bool $in_footer
     */
    public function register(FrontInclude $include)
    {
        $this->styleInclude->register($include);
    }

    /**
     * @param $handle string
     * @return bool
     */
    function deregister($handle)
    {
        return $this->styleInclude->deregister($handle);
    }

    function enqueue($location, FrontInclude $include)
     {
         return $this->styleInclude->enqueue($location,$include);
     }
    function dequeue($location, $handle)
    {
        return $this->styleInclude->dequeue($location,$handle);
    }
    /**
     * @param $handle string
     * @return bool
     */
    function isEnqueued($handle)
    {
        return $this->styleInclude->isEnqueued($handle);
    }
    /**
     * @param $handle string
     * @return bool
     */
    public function isRegistered($handle)
    {
        return $this->styleInclude->isRegistered($handle);
    }
    /**
     * @return bool
     */
    function init()
    {
        return $this->styleInclude->init();
    }
}
