<?php
namespace CLMVC\Core\Includes;

use CLMVC\Events\Filter;
/**
 * Class for queueing scripts. Only for internal consumption
 * @internal
 */
class QueuedScripts extends QueuedIncludes {
    private static $instance;

    public function __construct() {
        parent::__construct();
        Filter::register('stylesheets-frontend', array($this, 'render'));
    }

    /**
     * @return FrontInclude[handle]
     */
    function getRegisteredIncludes() {
        return ScriptIncludes::instance()->getRegistered();
    }

    function render($array) {
        /**
         * @var FrontInclude[] $queue
         */
        $queue = $this->getQueue('frontend');
        foreach ($queue as $include)
            $array[] = sprintf('<script src="%s"></script>', $include->getSrc());

        return $array;
    }

    public static function instance() {
        if(!isset(self::$instance) && empty(self::$instance))
            self::$instance = new QueuedScripts();
        return self::$instance;
    }
}