<?php

namespace CLMVC\Interfaces;

/**
 * Class IOption.
 */
interface IOption
{
    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public function isEmpty();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return mixed
     */
    public function delete();
}
