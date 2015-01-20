<?php
namespace CLMVC\Core;

/**
 * Class AoiSoraSettings
 */
class AoiSoraSettings {
    static $applications = array();

    /**
     * Register an application
     * @param string $name
     * @param string $load_path
     * @param string $version
     */
    static function addApplication($name,$load_path,$version) {
		$apps=self::$applications;
		if(!is_array($apps))
			$apps=array();
		$apps[$name]['path']=$load_path;
		$apps[$name]['version']=$version;
		Debug::Message('AddApplication');
        self::$applications = $apps;
    }

    /**
     * Remove an application
     * @param string $name
     */
    static function removeApplication($name) {
        $apps=self::$applications;
		unset($apps[$name]);
        self::$applications = $apps;
    }

    /**
     * Get the application version
     * @param string $name
     * @return bool
     */
    static function getApplicationVersion($name) {
        $apps = self::$applications;
		if(isset($apps[$name]['version']))
			return $apps[$name]['version'];
		return false;
	}

    /**
     * Retrieve registered applications
     * @return string[]
     */
    static function getApplications() {
		return self::$applications;
	}

    /**
     * Install an application
     * @param string $app
     */
    //TODO: add installation process
    static function installApplication($app) {
	}

    /**
     * Uninstall an application
     * @param string $app
     */
    //TODO: add uninstallation process
    static function uninstallApplication($app) {
	}

    /**
     * Check if application is installed
     * @param string $app
     * @return bool
     */
    static function installed($app) {
        return in_array($app,self::$applications);
	}
}