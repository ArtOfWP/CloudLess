<?php

/**
 * Class Debug
 */
class Debug{
    /**
     * Is debug active
     * @return bool
     */
    static function IsActive(){
		if(defined('DEBUG') && !(defined('DOING_AJAX') && DOING_AJAX))
			return DEBUG;
		return false;
	}

    /**
     * Log debug message
     * @param $message
     */
    static function Message($message){
		if(Debug::IsActive())
			if(defined('WRITE_TO_FILE') && WRITE_TO_FILE)
				file_put_contents(LOG_FILE,time()."\t".$message."\n",FILE_APPEND);
			else
				echo '<p>'.$message.'</p>';
	}

    /**
     * Log debug message with value
     * @param $message
     * @param $value
     */
    static function Value($message,$value){
		if(Debug::IsActive())			
			if(defined('WRITE_TO_FILE') && WRITE_TO_FILE){
				$value=is_array($value)|| is_object($value)?"\n".print_r($value,true):"\t".$value;
				file_put_contents(LOG_FILE,time()."\t".$message.$value."\n",FILE_APPEND);				
			}else{
			if(is_array($value) || is_object($value)){
				Debug::Message('<p><strong>'.$message.'</strong></p>');
				echo '<pre>';
				print_r($value);
				echo '</pre>';
			}else
				echo '<p><strong>'.$message.':</strong>  '.$value.'</p>';
			}
	}

    /**
     * Log backtrace
     */
    static function Backtrace(){
		if(Debug::IsActive()){		
			$thisfile = debug_backtrace();
			if(defined('WRITE_TO_FILE') && WRITE_TO_FILE){
				file_put_contents(LOG_FILE,time()."\tYou got here from ".$thisfile[0]['file']." on ".$thisfile[0]['line']."\n before that you were in ".$thisfile[1]['file']." on ".$thisfile[1]['line']."\n",FILE_APPEND);			
			}else{
				echo "<p>you got here from ".$thisfile[0]['file']." on ".$thisfile[0]['line'].'<br />';
				echo "before that you were in ".$thisfile[1]['file']." on ".$thisfile[1]['line'].'</p>';  
			}
		}
	}

    /**
     * Start timer and return it
     * @return RunningTime
     */
    static function timeIt(){
		$r= new RunningTime();
		$r->start();
		return $r;
	}
}

/**
 * Class RunningTime
 */
class RunningTime{
	private $starttime;
	private $endtime;

    /**
     * Start timer
     */
    function start(){
		$this->starttime= microtime(true);
	}

    /**
     * Stop timer
     */
    function stop(){
		$this->endtime= microtime(true);
	}

    /**
     * Retrieve the timelapsed
     * @return mixed
     */
    function timerun(){
		return $this->endtime-$this->starttime;
	}

    /**
     * Convert time to string
     * @return string
     */
    function __toString(){
		return $this->timerun().'';
	}
}