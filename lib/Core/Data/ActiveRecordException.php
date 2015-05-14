<?php

namespace CLMVC\Core\Data;

use Exception;

/**
 * Class ActiveRecordException.
 */
class ActiveRecordException extends Exception
{
    /**
     * Instantiates an exception.
     *
     * @param string $message
     * @param int    $code
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * Convert message to log string.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
