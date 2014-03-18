<?php
namespace CLMVC\Views;
/**
 * Class ShortCodeBase
 */
abstract class ShortCodeBase{
    /**
     * Initiate shortcode
     */
    function init(){
		$name=get_class($this);
		$sc=str_replace('shortcode','',strtolower($name));
		Shortcode::register($sc, array(&$this,'render'));
	}

    /**
     * Render shortcode
     * @param array $atts
     * @param string $content
     * @return mixed
     */
    abstract function render($atts, $content = '');
}