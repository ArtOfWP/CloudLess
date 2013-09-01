<?php
namespace CLMVC\Interfaces;

/**
 * Class IOptions
 */
interface IOptions {
    /**
     * @param $namespace
     * @return mixed
     */
    public function delete($namespace);

    /**
     * @param $namespace
     * @return mixed
     */
    public function load($namespace);

    /**
     * @param $namespace
     * @param $options
     * @return mixed
     */
    public function save($namespace,$options);
}
