<?php
	function array_key_exists_v($needle,$haystack){
		foreach($haystack as $key => $value)
			if($needle==$key)
				return $value;
		return false;
	}
	
	function array_search_key($search,$haystack){
		$array= array();
		foreach($haystack as $key => $value){
			$sub=stristr($key,$search);
			if($sub)
				$array[$key]=$value;
		}
		return $array;
	}