<?php
/**
 * User: andreas
 * Date: 2011-12-30
 * Time: 20:32
 */
interface IOptions
{
    public function delete($namespace);
    public function load($namespace);
    public function save($namespace,$options);
}
