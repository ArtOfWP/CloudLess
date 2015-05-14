<?php

namespace CLMVC\Core\Data;

use CLMVC\Helpers\ObjectUtility;

abstract class IUserMetas
{
    abstract public function install();

    public function save()
    {
        ObjectUtility::getPropertiesAndValues($this);
    }

    abstract public function delete();

    abstract public function getOne($user_id);

    abstract public function find($params, $limit);
}
