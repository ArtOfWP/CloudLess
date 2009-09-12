<?php

interface IDatabase{
	function connect($host,$database,$username,$password);
	function insert($row);
}
?>