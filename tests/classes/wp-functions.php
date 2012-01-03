<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 21:36
 */

global $actions;
global $options;
function add_action($name,$callback,$priority=10){
    global $action;
    $action=isset($action)?$action:array();
    $action[$name]=isset($action[$name])?$action[$name]:array();
    $action[$name][]=$callback;
}

function get_option($key){
    global $options;
    $options=isset($options)?$options:array();
    return isset($options[$key])?$options[$key]:false;
}
function add_option($key,$value){
    global $options;
    $options[$key]=$value;
}
function update_option($key,$value){
    global $options;
    $options[$key]=$value;
}
function delete_option($key){
    global $options;
    unset($options[$key]);
}