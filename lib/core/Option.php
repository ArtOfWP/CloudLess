<?php
class Option{
	static function create($name){
		return ViewEngine::createOption($name);
	}
}