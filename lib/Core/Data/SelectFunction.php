<?php

namespace CLMVC\Core\Data;

/**
 * Class Avg.
 */
class Avg extends SelectFunction
{
    private $as;

    /**
     * Create average SQL function.
     *
     * @param $column
     * @param bool $as
     */
    public function Avg($column, $as = false)
    {
        $this->setColumn($column);
        $this->as = $as;
    }

    /**
     * @param $column
     *
     * @return string
     */
    public function toSQL($column)
    {
        return "AVG($column) ".($this->as ? ' as '.$this->as : '');
    }
}

/**
 * Class Max.
 */
class Max extends SelectFunction
{
    private $as;

    /**
     * Create Max SQL function.
     *
     * @param $column
     * @param bool $as
     */
    public function Max($column, $as = false)
    {
        $this->setColumn(strtolower($column));
        $this->as = $as;
    }

    /**
     * @param $column
     *
     * @return string
     */
    public function toSQL($column)
    {
        return 'MAX('.strtolower($column).') '.($this->as ? ' as '.$this->as : '');
    }
}

/**
 * Class Min.
 */
class Min extends SelectFunction
{
    private $as;

    /**
     * Create Min SQL function.
     *
     * @param $column
     * @param bool $as
     */
    public function Min($column, $as = false)
    {
        $this->setColumn($column);
        $this->as = $as;
    }

    /**
     * @param $column
     *
     * @return string
     */
    public function toSQL($column)
    {
        return "MIN($column) ".($this->as ? ' as '.$this->as : '');
    }
}

/**
 * Class SelectFunction.
 */
abstract class SelectFunction
{
    private $column;

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    abstract public function toSQL($column);
}
