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
				Debug::Message('<strong>'.$message.'</strong>');
				echo '<ul>';
				foreach($value as $key => $val)
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
}
?>