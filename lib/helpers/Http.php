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
	static function getPage($url){
		$ch=curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  		
		$cache=curl_exec($ch);
		curl_close ($ch);
		return $cache;
	}
	static function save_file($url,$fullpath){
    $out = fopen($fullpath, 'wb');
    if ($out == FALSE){
      print "File not opened<br>";
      exit;
    }
    $ch = curl_init();
           
    curl_setopt($ch, CURLOPT_FILE, $out);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
   
    curl_close($ch); 	
	}

}
?>