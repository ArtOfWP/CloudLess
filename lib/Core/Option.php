<?php

namespace CLMVC\Core;

/**
 * Class Option.
 */
class Option
{
    private $key;
    private $defaultValue;
    private $value = null;
    private $type;

    public function __construct($key = '', $defaultValue = '', $type = 'string')
    {
        if ($key) {
            $this->setKey($key);
            $this->setDefaultValue($defaultValue);
            $this->setType($type);
        }
    }
    /**
     * @param string $key
     * @param string $defaultValue
     * @param string $type
     *
     * @return Option
     */
    public static function create($key = '', $defaultValue = '', $type = 'string')
    {
        $option = new self($key, $defaultValue, $type);

        return $option;
    }

    /**
     * Set the option key.
     *
     * @param $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set the options default value.
     *
     * @param $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        if (!isset($this->value)) {
            $this->value = $defaultValue;
        }
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the option key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the option value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the options default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the option value.
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Resets the value for option.
     */
    public function reset()
    {
        return $this->value = $this->getDefaultValue();
    }

    /**
     * Get the option type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the option type.
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Checks if option is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->value);
    }
}
