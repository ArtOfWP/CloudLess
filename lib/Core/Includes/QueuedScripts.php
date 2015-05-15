<?php

namespace CLMVC\Core\Includes;

use CLMVC\Events\Filter;

/**
 * Class for queueing scripts. Only for internal consumption.
 *
 * @internal
 */
class QueuedScripts extends QueuedIncludes
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
        return ScriptIncludes::instance()->getAllRegistered();
    }

    public function render($array)
    {
        return $this->renderIncludeTag('<script src="%s"></script>');
    }

    public static function instance()
    {
        if (!isset(self::$instance) && empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
