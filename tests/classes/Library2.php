<?php
namespace tests\classes;

/**
 * Class Library2
 * @package tests\classes
 */
class Library2
{
    private $database;

    /**
     * Library2 constructor.
     * @param ITestDatabase $idatabase
     */
    public function __construct(ITestDatabase $idatabase)
    {
        $this->database = $idatabase;
    }

    /**
     * @return ITestDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
