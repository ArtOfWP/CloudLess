<?php
namespace tests\classes;

/**
 * Class BookDatabase
 * @package tests\classes
 */
class BookDatabase implements ITestDatabase
{
    public $connectionString;

    /**
     * BookDatabase constructor.
     * @param string $connectionString
     */
    public function __construct($connectionString = "")
    {
        $this->connectionString=$connectionString;
    }
}
