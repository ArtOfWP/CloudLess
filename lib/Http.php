<?php
class Http{
	static function get_useragent(){
		return esc_js(esc_attr($_SERVER['HTTP_USER_AGENT']));
	}
	static function get_IP(){
		return esc_js(esc_attr($_SERVER['REMOTE_ADDR']));
	}
}
?>