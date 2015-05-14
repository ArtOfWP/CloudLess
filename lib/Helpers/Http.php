<?php
namespace CLMVC\Helpers;
/**
 * Class Http
 */
class Http {
    /**
     * Retrieve the user agent from request
     * @return string
     */
    public static function get_useragent(){
		return htmlspecialchars(strip_tags($_SERVER['HTTP_USER_AGENT']));
	}

    /**
     * Retrieve the requesting domain
     * @return string
     */
    public static function get_request_domain(){
		if(isset($_SERVER['HTTP_REFERER'])){
   		$parseUrl = parse_url(trim($_SERVER['HTTP_REFERER']));
		$domain=trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
		}
   		if(empty($domain))
   			$domain=trim($_SERVER['HTTP_HOST']);
		$domain=strtolower($domain);
		return $domain;
	}

    /**
     * @return string
     */
    public static function get_IP(){
		return htmlspecialchars(strip_tags($_SERVER['REMOTE_ADDR']));
	}

    /**
     * Save an image to
     * @param string $img
     * @param string $fullpath
     */
    public static function save_image($img, $fullpath='basename'){

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

    /**
     * Retrives a page
     * @param string $url
     * @param bool $referer
     * @return string
     */
    public static function getPage($url,$referer=false){
		$ch=curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  		
		if($referer)		
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		$cache=curl_exec($ch);
		curl_close ($ch);
		return $cache;
	}

    /**
     * Save a file to disk
     * @param $url
     * @param $fullpath
     */
    public static function save_file($url,$fullpath){
	    $out = fopen($fullpath, "wb");
	    if ($out == FALSE){
	      print "File not opened<br>";
	      exit;
	    }
	    $ch = curl_init();
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	    
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);	    
		curl_setopt($ch, CURLOPT_FAILONERROR, true);	    
	    curl_setopt($ch, CURLOPT_FILE, $out);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_exec($ch);
	   
	    curl_close($ch); 	
	}

	public static function requestPath() {
		return parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
	}

	public static function requestPathMatchesPath($path, $root=false) {
		$test = trim(stripslashes(self::requestPath())," /");
		if($root) {
			$pages=explode("/",$test);
			$test = array_shift( $pages );
		}
		return trim(stripslashes($path)," /") == $test;
	}

    public static function get_current_page($root = true) {
        $files = explode('/',trim(stripslashes(self::requestPath())," /"));
        if($root)
            return array_shift($files);
        return array_pop($files);
    }
}
