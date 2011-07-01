<?php
if(interface_exists('IFilter'))
	return;
interface IFilter{
	function perform($controller,$data);
}