<?php

namespace CLMVC\Core\Includes;

use CLMVC\Events\Filter;

/**
 * Class for queueing styles. Only for internal consumption.
 *
 * @internal
 */
class QueuedStyles extends QueuedIncludes
{
    private static $instance;

    public function __construct()
    {
        parent::__construct();
        Filter::register('stylesheets-frontend', array($this, 'render'));
    }

    /**
     * @return FrontInclude[]
     */
    public function getRegisteredIncludes()
    {
        return StyleIncludes::instance()->getAllRegistered();
    }

    public function render($array)
    {
        return $this->renderIncludeTag('<link href="%s" rel="stylesheet" type="text/css">');
    }

    public static function instance()
    {
        if (!isset(self::$instance) && empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
