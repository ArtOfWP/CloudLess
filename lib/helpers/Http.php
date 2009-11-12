<?php
class Http{
	static function get_useragent(){
		return esc_js(esc_attr($_SERVER['HTTP_USER_AGENT']));
	}
	static function get_IP(){
		return esc_js(esc_attr($_SERVER['REMOTE_ADDR']));
	}
	static function save_image($img,$fullpath='basename'){

		if($fullpath=='basename'){
			$fullpath = basename($img);
		}
		$ch = curl_init ($img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$rawdata=curl_exec($ch);
		curl_close ($ch);
		if(file_exists($fullpath)){
			unlink($fullpath);
		}
		$fp = fopen($fullpath,'x');
		fwrite($fp, $rawdata);
		fclose($fp);
	}

}
?>