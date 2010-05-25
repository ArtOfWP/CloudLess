<?php
class Debug{

	static function IsActive(){
		if(defined('DEBUG'))
			return DEBUG;
		return false;
	}
	static function Message($message){
		if(Debug::IsActive())
			echo '<p>'.$message.'</p>';
	}
	static function Value($message,$value){
		if(Debug::IsActive())
			if(is_array($value)){
				Debug::Message('<p><strong>'.$message.'</strong></p>');
				echo '<ul>';
				foreach($value as $key => $val)
					if(is_array($val)){
						echo '<li>'.$key.':<ul>';
							foreach($val as $key2 => $val2)
		 						echo '<li>'.$key2.' : '.$val2.'</li>';
						echo '</ul></li>';
					}
					else
 						echo '<li>'.$key.' : '.$val.'</li>';
				echo '</ul>';
			}else
				echo '<p><strong>'.$message.':</strong>  '.$value.'</p>';
	}
	static function Backtrace(){
		if(Debug::IsActive()){		
			$thisfile = debug_backtrace();
			echo "<p>you got here from ".$thisfile[0]['file']." on ".$thisfile[0]['line'].'<br />';
			echo "before that you were in ".$thisfile[1]['file']." on ".$thisfile[1]['line'].'</p>';  
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
?>