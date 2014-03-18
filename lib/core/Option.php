<?php
namespace CLMVC\Core;
use CLMVC\Interfaces\IOption;

/**
 * Class Option
 *
 */
class Option implements IOption {
    /**
     * @var IOption
     */
    private $iOption;
    /**
     * @param string $key
     * @param string $defaultValue
     * @param string $type
     * @return Option
     */
    static function create($key = '',$defaultValue='' ,$type='string'){
        $option = new Option();
        if ($key) {
            $option->setKey($key);
            $option->setDefaultValue($defaultValue);
            $option->setType($type);
            $option->init();
        }
        return $option;
    }

    /**
     * @param IOption $iOption
     */
    public function __construct(IOption $iOption=NULL) {
        if($iOption)
            $this->iOption=$iOption;
        else
            $this->iOption=Container::instance()->make('CLMVC\\Interfaces\\IOption');
    }

    /**
     * Set the option key
     * @param $key
     */
    public function setKey($key) {
        $this->iOption->setKey($key);
    }

    /**
     * Set the options default value
     * @param $defaultValue
     */
    public function setDefaultValue($defaultValue) {
        if(!isset($this->value))
            $this->iOption->setValue($defaultValue);
        $this->iOption->setDefaultValue($defaultValue);
    }

    /**
     * Returns the option key
     * @return mixed
     */
    public function getKey() {
        return $this->iOption->getKey();
    }

    /**
     * Returns the option value
     * @return mixed
     */
    public function getValue() {
        return $this->iOption->getValue();
    }

    /**
     * Returns the options default value
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->iOption->getDefaultValue();
    }

    /**
     * Set the option value
     * @param $value
     */
    public function setValue($value) {
        $this->iOption->setValue($value);
    }

    /**
     * Sets an option property
     * @param $property
     * @param $value
     */
    function __set($property,$value){
        $this->iOption->$property=$value;
    }

    /**
     * Returns a option property
     * @param $property
     * @return mixed
     */
    function __get($property) {
        return $this->iOption->$property;
    }

    /**
     * Resets the value for option.
     */
    function reset() {
        return $this->iOption->setValue($this->getDefaultValue());
    }

    /**
     * Get the option type
     * @return string
     */
    public function getType() {
        return $this->iOption->getType();
    }

    /**
     * Set the option type
     * @param $type
     */
    public function setType($type) {
        $this->iOption->setType($type);
    }

    /**
     * Initialize option
     */
    function init() {
        $this->iOption->init();
    }

    /**
     * Checks if option is empty
     * @return bool
     */
    function isEmpty() {
        return $this->iOption->isEmpty();
    }

    /**
     * Saves option
     */
    function save() {
        $this->iOption->save();
    }

    /**
     * Deletes option
     */
    function delete() {
        $this->iOption->delete();
    }
}
