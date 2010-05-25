<?php
interface IOption{
	function init();	
	function isEmpty();
	function save();
	function delete();
}