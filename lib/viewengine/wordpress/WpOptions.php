<?php
/**
 * User: andreas
 * Date: 2011-12-30
 * Time: 20:31
 */

class WpOptions implements IOptions
{
    function delete($namespace){
        delete_option($namespace);
    }
     function load($namespace){
         $options=get_option($namespace);
         if(empty($options))
             $options=array();
         return $options;
     }

    function save($namespace,$options){
        if(get_option($namespace))
    	    update_option($namespace,$options);
        else
    	    add_option($namespace,$options);
    }

}
