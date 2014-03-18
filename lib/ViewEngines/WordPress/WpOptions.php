<?php
namespace CLMVC\ViewEngines\WordPress;
use CLMVC\Interfaces\IOptions;

/**
 * Class WpOptions
 */
class WpOptions implements IOptions
{
    /**
     * @param $namespace
     * @return bool
     */
    function delete($namespace){
        return delete_option($namespace);
    }

    /**
     * @param $namespace
     * @return array|bool
     */
    function load($namespace){
         $options=get_option($namespace);
         if(empty($options))
             $options=array();
         return $options;
     }

    /**
     * @param $namespace
     * @param $options
     * @return bool
     */
    function save($namespace,$options){
        if(get_option($namespace))
    	    return update_option($namespace,$options);
        else
            return add_option($namespace,$options);
    }

}
