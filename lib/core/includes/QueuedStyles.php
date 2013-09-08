<?php
namespace CLMVC\Core\Includes;

use CLMVC\Events\Filter;
/**
 * Class for queueing styles. Only for internal consumption
 * @internal
 */
class QueuedStyles extends QueuedIncludes {
    private static $instance;

    public function __construct() {
        parent::__construct();
        Filter::register('stylesheets-frontend', array($this, 'render'));
    }

    /**
     * @return FrontInclude[handle]
     */
    function getRegisteredIncludes() {
        return StyleIncludes::instance()->getRegistered();
    }

    function render($array) {
        /**
         * @var FrontInclude[] $queue
         */
        $queue = $this->getQueue('frontend');
        foreach ($queue as $include)
            $array[] = sprintf('<link href="%s" rel="stylesheet" type="text/css">', $include->getSrc());

        return $array;    }

    public static function instance() {
        if(!isset(self::$instance) && empty(self::$instance))
            self::$instance = new QueuedStyles();
        return self::$instance;
    }
}