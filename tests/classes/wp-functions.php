<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 21:36
 */

global $actions;
function add_action($name,$callback,$priority=10){
    global $action;
    $action=isset($action)?$action:array();
    $action[$name]=isset($action[$name])?$action[$name]:array();
    $action[$name][]=$callback;
}