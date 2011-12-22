<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 16:29
 */
class BookDatabase implements ITestDatabase
{
    public $connectionString;
    function __construct($connectionString=""){
        $this->connectionString=$connectionString;
    }
}
interface ITestDatabase{

}