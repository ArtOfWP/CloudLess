<?php
namespace tests\classes;

/**
 * Class SubClass
 * @package tests\classes
 */
class SubClass extends ParentClass
{
    /**
     * SubClass constructor.
     */
    public function __construct()
    {
        parent::__construct('someparam');
    }
}
