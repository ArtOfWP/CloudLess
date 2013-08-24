<?php

/**
 * Class IDatabase
 */
interface IDatabase{
    /**
     * @param $host
     * @param $database
     * @param $username
     * @param $password
     * @return mixed
     */
    function connect($host,$database,$username,$password);

    /**
     * @param $row
     * @return mixed
     */
    function insert($row);

    /**
     * @param $row
     * @param $restriction
     * @return mixed
     */
    function update($row,$restriction);

    /**
     * @param $query
     * @return mixed
     */
    function query($query);

    /**
     * @param $query
     * @return mixed
     */
    function delete($query);

    /**
     * @param $sql
     * @return mixed
     */
    function execute($sql);

    /**
     * @param $object
     * @return mixed
     */
    function dropTable($object);

    /**
     * @param $object
     * @return mixed
     */
    function createTable($object);
}
