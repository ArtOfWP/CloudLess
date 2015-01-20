<?php

namespace CLMVC\Core\Data;


use CLMVC\Helpers\ObjectUtility;

abstract class IUserMetas {
    public abstract function install();

    public function save() {
        ObjectUtility::getPropertiesAndValues($this);

    }

    public abstract function delete();

    public abstract function getOne($user_id);

    public abstract  function find($params, $limit);
}
