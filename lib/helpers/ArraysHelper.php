<?php
	function array_key_has_value($key,$value,$haystack){
		foreach($haystack as $k => $val)
			if($key==$k)
				if(is_array($value))
					return in_array($val,$value);
				else
					return $val==$value;
		return false;
	}
	function array_key_exists_v($needle,$haystack){
        if($haystack)
    		foreach($haystack as $key => $value)
	    		if($needle===$key)
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