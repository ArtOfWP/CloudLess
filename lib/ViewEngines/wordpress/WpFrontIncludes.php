<?php
namespace CLMVC\ViewEngines\WordPress;
use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Interfaces\IIncludes;

abstract class WpFrontIncludes implements IIncludes
{
    private $includes=array();
    private $queue=array();
    private $dequeue=array();
    private $unincludes=array();
    function register(FrontInclude $include) {
        $this->includes[$include->getHandle()]=$include;
    }

    function deregister($handle) {
        if(isset($this->includes[$handle]))
            unset($this->includes[$handle]);
        $this->unincludes[]=$handle;
        return true;
    }

    function enqueue($location, $handle) {
        if(!isset($this->queue[$location])|| empty($this->queue[$location]))
            $this->queue[$location]=array();
        $this->queue[$location][$handle]=$this->includes[$handle];
    }

    function dequeue($location, $handle) {
        if(isset($this->queue[$location][$handle]))
            unset($this->queue[$location][$handle]);
        $this->dequeue[$location]=$handle;
        return true;
    }

    function isRegistered($handle) {
       return isset($this->includes[$handle]) && !empty($this->includes[$handle]);
    }

    function isEnqueued($handle) {
        foreach($this->queue as $includes)
            foreach($includes as $qHandle => $include)
                if($handle==$qHandle)
                    return true;
        return false;
    }

    function init() {
        add_action('init',array($this,'registerIncludes'));
        add_action('login_enqueue_scripts',array($this,'loginEnqueueIncludes'));
        add_action('admin_enqueue_scripts',array($this,'adminEnqueueIncludes'));
        add_action('wp_enqueue_scripts',array($this,'wpEnqueueIncludes'));
        return true;
    }

    function registerIncludes(){
        /**
         * @var $include FrontInclude
         */
        foreach($this->includes as $include){
            $this->registerInclude($include);
        }
        foreach($this->unincludes as $include){
            $this->deregisterInclude($include);
        }
    }
    function loginEnqueueIncludes() {
        $this->enqueueLocation('login');
    }
    function adminEnqueueIncludes() {
        $this->enqueueLocation('administration');
    }
    function wpEnqueueIncludes() {
        $this->enqueueLocation('frontend');
    }
    private function enqueueLocation($location) {
        /**
        * @var $include FrontInclude
        */
        if(isset($this->queue[$location] ))
            foreach($this->queue[$location] as $include)
                $this->enqueueInclude($include);
        if(isset($this->dequeue[$location] ))
            foreach($this->dequeue[$location] as $include)
                $this->dequeueInclude($include);
    }

    abstract function enqueueInclude(FrontInclude $include);
    abstract function registerInclude(FrontInclude $include);
    abstract function dequeueInclude($includeHandle);
    abstract function deregisterInclude($includeHandle);
}
