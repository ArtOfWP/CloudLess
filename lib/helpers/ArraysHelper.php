<?php
	function array_key_exists_v($needle,$haystack){
		foreach($haystack as $key => $value)
			if($needle==$key)
				return $value;
		return false;
	}
?>