<?php

namespace CLMVC\Interfaces;

/**
 * Class IDatabase.
 */
interface IDatabase
{
    /**
     * @param $host
     * @param $database
     * @param $username
     * @param $password
     *
     * @return mixed
     */
    public function connect($host, $database, $username, $password);

    /**
     * @param $row
     *
     * @return mixed
     */
    public function insert($row);

    /**
     * @param $row
     * @param $restriction
     *
     * @return mixed
     */
    public function update($row, $restriction);

    /**
     * @param $query
     *
     * @return mixed
     */
    public function query($query);

    /**
     * @param $query
     *
     * @return mixed
     */
    public function delete($query);

    /**
     * @param $sql
     *
     * @return mixed
     */
    public function execute($sql);

    /**
     * @param $object
     *
     * @return mixed
     */
    public function dropTable($object);

    /**
     * @param $object
     *
     * @return mixed
     */
    public function createTable($object);
}
