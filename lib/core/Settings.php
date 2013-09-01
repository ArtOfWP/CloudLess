<?php
namespace CLMVC\Core;

/**
 * Class AoiSoraSettings
 */
class AoiSoraSettings{
    /**
     * Register an application
     * @param string $name
     * @param string $load_path
     * @param string $version
     */
    static function addApplication($name,$load_path,$version) {
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		if(!is_array($apps))
			$apps=array();
		$apps[$name]['path']=$load_path;
		$apps[$name]['version']=$version;
		$aoiSoraApp->options->updateValue('applications',$apps);
		$aoiSoraApp->options->save();
		Debug::Message('AddApplication');
	}

    /**
     * Remove an application
     * @param string $name
     */
    static function removeApplication($name) {
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		unset($apps[$name]);
		$aoiSoraApp->options->updateValue('applications',$apps);
		$aoiSoraApp->options->save();
		Debug::Message('RemoveApplication');
		Debug::Value('Options',$aoiSoraApp->options->getArray());		
	}

    /**
     * Get the application version
     * @param string $name
     * @return bool
     */
    static function getApplicationVersion($name) {
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->applications;
		if(isset($apps[$name]['version']))
			return $apps[$name]['version'];
		return false;
	}

    /**
     * Retrieve registered applications
     * @return string[]
     */
    static function getApplications() {
		$aoiSoraApp = AoiSora::instance();
		return $aoiSoraApp->options->applications;
	}

    /**
     * Install an application
     * @param string $app
     */
    static function installApplication($app) {
		Debug::Message('Install application');
		$aoiSoraApp = AoiSora::instance();
		$inst=	$aoiSoraApp->options->installed;
		if(!is_array($inst))
			$inst=array();
		$inst[]=$app;
		$aoiSoraApp->options->updateValue('installed',$inst);
		$aoiSoraApp->options->save();
	}

    /**
     * Uninstall an application
     * @param string $app
     */
    static function uninstallApplication($app) {
		Debug::Message('Uninstall application');		
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->installed;
		$apps = array_diff($apps,array($app));
		$apps=array_values($apps);
		$aoiSoraApp->options->updateValue('installed',$apps);
		$aoiSoraApp->options->save();		
	}

    /**
     * Check if application is installed
     * @param string $app
     * @return bool
     */
    static function installed($app) {
		$aoiSoraApp = AoiSora::instance();
		$apps=$aoiSoraApp->options->installed;
        return in_array($app,(array)$apps);
	}
}