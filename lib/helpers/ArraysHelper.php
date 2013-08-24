<?php

/**
 * Checks if an array has a specific value
 * @param $key
 * @param $value
 * @param $haystack
 * @return bool
 */
function array_key_has_value($key,$value,$haystack){
		foreach($haystack as $k => $val)
			if($key==$k)
				if(is_array($value))
					return in_array($val,$value);
				else
					return $val==$value;
		return false;
	}

/**
 * Checks if an array has a needle and if so returns its value.
 * @param string $needle
 * @param array $haystack
 * @return bool|mixed
 */
function array_key_exists_v($needle,$haystack){
    if($haystack)
        foreach($haystack as $key => $value)
            if($needle===$key)
                return $value;
    return false;
}

/**
 * Searches an array to see if keys matching the search string exists if so returns all matching key value pairs
 * @param string $search
 * @param array $haystack
 * @return array
 */
function array_search_key($search,$haystack){
		$array= array();
		foreach($haystack as $key => $value){
			$sub=stristr($key,$search);
			if($sub)
				$array[$key]=$value;
		}
		return $array;
	}

/**
 * Searches an array to see if values matching the search string exists if so returns all matching keys
 * @param string $search
 * @param string $haystack
 * @return array
 */
function array_search_keys_value($search,$haystack){
	$array= array();
	foreach($haystack as $key=>$value){
		$sub=strstr($value,$search);
		if($sub)
			$array[]=$key;
	}
	return $array;
}