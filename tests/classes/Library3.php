<?php
namespace tests\classes;

/**
 * Class Library2
 * @package tests\classes
 */
class Library3
{
    private $database;
    private $some_other_param;
    /**
     * @var string
     */
    private $optional_param;

    /**
     * Library3 constructor.
     * @param ITestDatabase $idatabase
     * @param $some_other_param
     * @param string $optional_param
     */
    public function __construct(ITestDatabase $idatabase, $some_other_param, $optional_param = 'optional')
    {
        $this->database = $idatabase;
        $this->some_other_param = $some_other_param;
        $this->optional_param = $optional_param;
    }

    /**
     * @return ITestDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getSomeOtherParam()
    {
        return $this->some_other_param;
    }

    /**
     * @return string
     */
    public function getOptionalParam()
    {
        return $this->optional_param;
    }
}
