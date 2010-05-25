<?php
interface IDatabase{
	function connect($host,$database,$username,$password);
	function insert($row);
	function update($row,$restriction);
	function query($query);
	function delete($query);
	function execute($sql);
	function dropTable($object);
	function createTable($object);		
}
?>