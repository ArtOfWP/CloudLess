<?php
class Debug{
	static function IsActive(){
		if(defined('DEBUG') && !(defined('DOING_AJAX') && DOING_AJAX))
			return DEBUG;
		return false;
	}
	static function Message($message){
		if(Debug::IsActive())
			if(defined('WRITE_TO_FILE') && WRITE_TO_FILE)
				file_put_contents(LOG_FILE,time()."\t".$message."\n",FILE_APPEND);
			else
				echo '<p>'.$message.'</p>';
	}
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
	static function timeIt(){
		$r= new RunningTime();
		$r->start();
		return $r;
	}
}
class RunningTime{
	private $starttime;
	private $endtime;
	function start(){
		$this->starttime= microtime(true);
	}
	function stop(){
		$this->endtime= microtime(true);
	}
	function timerun(){
		return $this->endtime-$this->starttime;
	}
	function __toString(){
		return $this->timerun().'';
	}
}