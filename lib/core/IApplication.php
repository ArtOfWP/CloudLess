<?php
interface IApplication{
//	function register_query_vars($public_query_vars);
//	function register_settings();
//	function after_plugin_row($plugin_file, $plugin_data);
//	function plugin_page_links($links);
	function init();
	function activate();
	function deactivate();
	function install();
	function uninstall();
	function delete();
	function print_admin_styles();
	function print_admin_scripts();
	function print_styles();	
	function print_scripts();
}
?>