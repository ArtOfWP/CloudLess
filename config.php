<?php
/**
 * Configures AoiSora for WordPress use
 */
define('VIEWENGINE','wordpress');
define('LIB','/app/');
define('DOMAIN',LIB.'domain/');
define('VIEWS',LIB.'Views/');
define('CONTROLLERS',LIB.'/Controllers/');

if(!defined('DEBUG'))
	define('DEBUG',false);
if(!defined('SQLDEBUG'))
	define('SQLDEBUG',false);
define('WRITE_TO_FILE',false);
define('LOG_FILE',ABSPATH.'logfile.txt');
if(file_exists(LOG_FILE))
	unlink(LOG_FILE);
if(!defined('NO_REDIRECT'))
	define('NO_REDIRECT',false);