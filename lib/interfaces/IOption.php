<?php

/**
 * Class IOption
 */
interface IOption{
    /**
     * @return mixed
     */
    function init();

    /**
     * @return mixed
     */
    function isEmpty();

    /**
     * @return mixed
     */
    function save();

    /**
     * @return mixed
     */
    function delete();
}