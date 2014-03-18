<?php

namespace CLMVC\Core\Data;


use CLMVC\Helpers\ObjectUtility;

abstract class IUserMetas {
    protected $user_id;
    public abstract function install();

    public function save() {
        ObjectUtility::getPropertiesAndValues($this);

    }

    public function delete() {
    }

    public function getOne($user_id) {

    }

    public function find($params, $limit) {

    }
} 
