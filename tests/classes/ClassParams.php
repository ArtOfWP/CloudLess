<?php
namespace tests\classes;

/**
 * Class ClassParams
 * @package classes
 */
class ClassParams
{
    /**
     * @var ParentClass
     */
    private $class;
    /**
     * @var ParentClass
     */
    private $class2;

    /**
     * ClassParams constructor.
     * @param ParentClass $class
     * @param ParentClass $class2
     */
    public function __construct(ParentClass $class, ParentClass $class2)
    {

        $this->class = $class;
        $this->class2 = $class2;
    }

    /**
     * @return ParentClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return ParentClass
     */
    public function getClass2()
    {
        return $this->class2;
    }
}
