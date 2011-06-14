<?php
abstract class ShortCodeBase{
	function init(){
		$name=get_class($this);
		$sc=str_replace('shortcode','',strtolower($name));
		Shortcode::register($sc, array(&$this,'render'));
	}
}