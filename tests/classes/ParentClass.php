<?php
namespace tests\classes;

/**
 * Class ParentClass
 * @package tests\classes
 */
class ParentClass
{
    private $some_param;

    /**
     * ParentClass constructor.
     * @param $some_param
     */
    public function __construct($some_param = '')
    {
        $this->some_param = $some_param;
    }

    /**
     * @return mixed
     */
    public function getSomeParam()
    {
        return $this->some_param;
    }

    /**
     * @param mixed $some_param
     */
    public function setSomeParam($some_param)
    {
        $this->some_param = $some_param;
    }
}
