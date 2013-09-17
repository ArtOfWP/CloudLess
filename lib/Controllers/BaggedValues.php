<?php
namespace CLMVC\Controllers;


class BaggedValues {
    private $data = array();
    public function  __get($name) {
        if(array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    public function  __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function  __isset($name) {
        return array_key_exists($name, $this->data);
    }

    public function  __unset($name) {
        unset($this->data[$name]);
    }

    public function toArray() {
        return $this->data;
    }
} 