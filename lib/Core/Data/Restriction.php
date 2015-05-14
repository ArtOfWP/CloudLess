<?php

namespace CLMVC\Core\Data;

use CLMVC\Core\Debug;

class Restriction
{
    public $table;
    public $column;
    public $foreignTable;
    public $foreignColumn;
    public $value;
    public $hasValue = false;
    public $values = array();
    public $parameters = array();
    public $columns;
    public $method;
    public $placement;

    public static $LEFT = 0;
    public static $BOTH = 1;
    public static $RIGHT = 2;

    /**
     * Match keywords against columns.
     *
     *@param $columns
     * @param $keywords
     *
     *@return Restriction
     */
    public static function Match($columns, $keywords)
    {
        $r = new self();
        $r->method = 'MATCH';
        $r->columns = $columns;
        $r->setParameter('matchParams'.sizeof($columns), trim($keywords));

        return $r;
    }

    /**
     * Greater than or equal to.
     *
     *@param string $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty value is an ActiveRecordBase object use property
     *
     *@return Restriction
     */
    public static function Ge($property, $value, $isProperty = false)
    {
        $r = self::Eq($property, $value, $isProperty);
        $r->method = '>=';

        return $r;
    }

    /**
     * Less than or equal too.
     *
     *@param string $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty
     *
     * @return Restriction
     */
    public static function Le($property, $value, $isProperty = false)
    {
        $r = self::Ge($property, $value, $isProperty);
        $r->method = '<=';

        return $r;
    }

    /**
     * Less than.
     *
     *@param string $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty
     *
     * @return Restriction
     */
    public static function Lt($property, $value, $isProperty = false)
    {
        $r = self::Eq($property, $value, $isProperty);
        $r->method = '<';

        return $r;
    }

    /**
     * Greater than.
     *
     *@param string $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty
     *
     * @return Restriction
     */
    public static function Gt($property, $value, $isProperty = false)
    {
        $r = self::Eq($property, $value, $isProperty);
        $r->method = '>';

        return $r;
    }

    /**
     * Equal too.
     *
     *@param string|object $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty
     *
     *@return Restriction
     */
    public static function Eq($property, $value, $isProperty = false)
    {
        global $db_prefix;
        $r = new self();
        if ($property instanceof ActiveRecordBase) {
            $r->table = $db_prefix.strtolower(get_class($property));
            $r->column = 'Id';
        } else {
            $r->column = strtolower($property);
        }
        $r->method = '=';
        if ($isProperty) {
            $p = explode('.', $value);
            if (sizeof($p) > 1) {
                $r->foreignTable = $db_prefix.strtolower($p[0]);
                $r->foreignColumn = strtolower($p[1]);
            } else {
                $r->column = $p[0];
            }

            return $r;
        }
        if ($value instanceof ActiveRecordBase) {
            $r->value = $value->getId();
        } else {
            $r->value = $value;
        }
        $r->hasValue = true;
        $r->setParameter(str_replace('.', '', $r->column), $r->value);

        return $r;
    }

    /**
     * Not equal too.
     *
     *@param string $property column to check against
     * @param mixed $value      value to compare against
     * @param bool  $isProperty
     *
     *@return Restriction
     */
    public static function NotEq($property, $value, $isProperty = false)
    {
        global $db_prefix;
        $r = new self();
        if ($property instanceof ActiveRecordBase) {
            $r->table = $db_prefix.strtolower(get_class($property));
            $r->column = 'id';
        } else {
            $r->column = strtolower($property);
        }
        $r->method = '<>';
        if ($isProperty) {
            $p = explode('.', $value);
            if (sizeof($p) > 1) {
                $r->foreignTable = $db_prefix.strtolower($p[0]);
                $r->foreignColumn = strtolower($p[1]);
            } else {
                $r->column = $p[0];
            }

            return $r;
        }

        if ($value instanceof ActiveRecordBase) {
            $r->value = $value->getId();
        } else {
            $r->value = $value;
        }
        $r->hasValue = true;
        $r->setParameter($r->column, $r->value);

        return $r;
    }

    /**
     * Equal too compares ActiveRecordBase.
     *
     *@param $class
     * @param string $property   column to check against
     * @param mixed  $value      value to compare against
     * @param bool   $isProperty
     *
     *@return Restriction
     */
    public static function EqP($class, $property, $value, $isProperty = false)
    {
        global $db_prefix;
        $r = new self();
        if ($class instanceof ActiveRecordBase) {
            $class = get_class($class);
        }
        $r->table = $db_prefix.strtolower($class);
        $r->column = $property;
        $r->method = '=';
        if ($isProperty) {
            $p = explode('.', $value);
            if (sizeof($p) > 1) {
                $r->foreignTable = $db_prefix.strtolower($p[0]);
                $r->foreignColumn = strtolower($p[1]);
            } else {
                $r->column = $p[0];
            }

            return $r;
        }
        if ($value instanceof ActiveRecordBase) {
            $r->value = $value->getId();
        } else {
            $r->value = $value;
        }
        $r->hasValue = true;
        $r->setParameter($r->column, $r->value);

        return $r;
    }

    /**
     * column value in list of values.
     *
     *@param string $property
     * @param $values
     *
     *@return Restriction
     */
    public static function In($property, $values)
    {
        global $db_prefix;
        $r = new self();
        if ($property instanceof ActiveRecordBase) {
            $r->table = $db_prefix.strtolower(get_class($property));
            $r->column = 'id';
        } else {
            $r->column = strtolower($property);
        }
        $r->method = ' IN ';
/*		if($isProperty){
            $p=explode('.',$value);
            if(sizeof($p)>1){
                $r->foreigntable=$db_prefix.strtolower($p[0]);
                $r->foreigncolumn=strtolower($p[1]);
            }else
                $r->column=$p[0];
        }else{*/
        $count = 0;
        foreach ($values as $value) {
            if ($value instanceof ActiveRecordBase) {
                $tempValue = $value->getId();
            } else {
                $tempValue = $value;
            }
            $r->values[] = $tempValue;
            $param = $r->column.($count++);
            $r->setParameter($param, $tempValue);
        }
//		}
        return $r;
    }

    /**
     * Like.
     *
     *@param $property
     * @param $value
     * @param int $placement
     *
     *@return Restriction
     */
    public static function Like($property, $value, $placement = 0)
    {
        $r = new self();
        $r->column = strtolower($property);
        $r->method = 'LIKE';
        $r->placement = $placement;
        $r->value = $value;
        $r->setParameter($r->column, $value);
        $r->hasValue = true;

        return $r;
    }

    /**
     * AND restriction.
     *
     * @return Restriction
     */
    public static function _And()
    {
        $r = new self();
        $r->method = ' AND ';

        return $r;
    }

    /**
     * OR restriction.
     *
     * @return Restriction
     */
    public static function _Or()
    {
        $r = new self();
        $r->method = ' OR ';

        return $r;
    }

    /**
     * If has value.
     *
     * @return bool
     */
    public function hasValue()
    {
        return $this->hasValue;
    }

    /**
     * Retrieve value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set parameter.
     *
     * @param string $param
     * @param $value
     */
    public function setParameter($param, $value)
    {
        Debug::Value('Set param :'.$param, $value);
        $this->parameters[":$param"] = $value;
    }

    /**
     * remove parameter.
     *
     * @param $param
     */
    public function removeParameter($param)
    {
        $param = trim($param, ':');
        unset($this->parameters[":$param"]);
    }

    /**
     * Return parameter.
     *
     * @param bool $key
     *
     * @return array
     */
    public function getParameter($key = false)
    {
        if ($key) {
            return $this->parameters[":$key"];
        }

        return $this->parameters;
    }

    /**
     * Return parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Convert restriction to SQL counterpart.
     *
     * @return string
     */
    public function toSQL()
    {
        switch ($this->method) {
            case 'LIKE':
                $front = '%';
                $back = '%';
                if ($this->placement == self::$LEFT) {
                    $front = '%';
                } elseif ($this->placement == self::$RIGHT) {
                    $back = '%';
                }
                $param_keys = array_keys($this->getParameters());
                $param = array_pop($param_keys);
                $sql = $this->addMark($this->column).' LIKE '."concat('$front',".$param.",'$back')";

                return $sql;
            case 'MATCH':
                $sql = ' MATCH(';
                $count = count($this->columns);
                $columns = $this->columns;
                for ($i = 0;$i < $count;$i++) {
                    $columns[$i] = $this->addMark($columns[$i]);
                }

                    $sql .= implode(',', $columns);
                $sql .= ') AGAINST(';
                $param_keys = array_keys($this->getParameters());
                $param = array_pop($param_keys);
                $sql .= $param;
                $sql .= ' IN BOOLEAN MODE)';

                return $sql;
            case ' IN ':
                $sql = '';
                if ($this->table) {
                    $sql .= $this->addMark($this->table).'.';
                }
                $sql .= $this->addMark($this->column).$this->method;
                $sql .= '(';
                $sql .= implode(',', array_keys($this->getParameters()));
                $sql .= ')';

                return $sql;
            case '>=':
            case '>':
            case '<=':
            case '<':
            case '<>':
            case '=':
                $sql = '';
                if ($this->table) {
                    $sql .= $this->addMark($this->table).'.';
                }
                $sql .= $this->addMark($this->column).$this->method;
                if ($this->hasValue()) {
                    $sql .= array_pop(array_keys($this->getParameters()));
                } else {
                    $sql .= $this->addMark($this->foreignTable).'.'.$this->addMark($this->foreignColumn);
                }

                return $sql;
            case ' OR ':
            case ' AND ':
                return $this->method;
        }
        trigger_error("{$this->method} is not a valid restriction.", E_USER_WARNING);

        return;
    }

    /**
     * @param $ct
     *
     * @return string
     */
    private function addMark($ct)
    {
        return '`'.strtolower($ct).'`';
    }

    const BOTH = 1;
    const LEFT = 0;
    const RIGHT = 2;
}
