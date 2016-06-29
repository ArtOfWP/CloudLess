<?php
namespace tests\classes;

/**
 * Class Library
 * @package tests
 */
class Library
{
    private $database;

    /**
     * Library constructor.
     * @param BookDatabase $BookDatabase
     */
    public function __construct(BookDatabase $BookDatabase)
    {
        $this->database = $BookDatabase;
    }

    /**
     * @return BookDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
