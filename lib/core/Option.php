<?php
class Option{
    /**
     * @static
     * @param $name
     * @return IOption
     */
	static function create($name){
		return ViewEngine::createOption($name);
	}
}