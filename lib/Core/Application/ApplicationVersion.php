<?php
namespace CLMVC\Core\Application;

use CLMVC\Core\AoiSoraSettings;
use CLMVC\Core\Application\ApplicationBase;
use CLMVC\Core\Security\Security;
use CLMVC\Events\Filter;
use CLMVC\Events\Hook;
use CLMVC\Helpers\Http;

class ApplicationVersion {
    /**
     * @var ApplicationBase
     */
    private $application;
    private $version;
    private $version_info_link;
    private $update_site;
    private $update_site_extra;
    private $slug;

    public function __construct(ApplicationBase $application, $version, $version_info_link, $update_site, $update_site_extra, $slug) {
        $this->application = $application;
        $this->version = $version;
        $this->version_info_link = $version_info_link;
        $this->update_site = $update_site;
        $this->update_site_extra = $update_site_extra;
        $this->slug = $slug;
    }

    public function init() {
        if(Security::isAdmin()){
            Filter::register('set_plugin_has_updates', array($this, 'siteTransientUpdatePlugins'));
            Hook::register('set_plugin_has_updates', array($this, 'transientUpdatePlugins'));

            if(isset($_GET['plugin']) && $_GET['plugin']==$this->application->getName())
                Hook::register('install_plugins_pre_plugin-information',array($this,'versionInformation'));
            $this->application->onInitUpdate();
            $oldVersion=AoiSoraSettings::getApplicationVersion($this->application->getName());
            if($this->application->installed() && version_compare($oldVersion,$this->version,'<')){
                AoiSoraSettings::addApplication($this->application->getName(),$this->application->getInstallDirectory(),$this->version);
                $this->application->update();
            }
            if($this->update_site && isset($_REQUEST['action']) && 'upgrade-plugin'==$_REQUEST['action'] && isset($_REQUEST['plugin']) && urldecode($_REQUEST['plugin'])==$this->application->getInstallName())
                Filter::register('http_request_args',array($this,'addUpdateUrl'),10);
        }
    }

    /**
     *

    private function update(){
        $this->application->onUpdate();
        $updatePath=trim($this->application->dir,'/').'/app/updates/'.$this->version.'.php';
        if(file_exists('/'.$updatePath))
            include('/'.$updatePath);
        else if(file_exists($updatePath))
            include($updatePath);
    }
     */

    /**
     *
     */
    function versionInformation(){
        if(!$this->version_info_link)
            return;
        $response=Http::getPage($this->version_info_link.'&id='.$this->slug);
        die(nl2br($response));
    }

    //TODO: Remove WordPress dependency
    /**
     * @return array|mixed
     */
    function getVersionInfo(){
        global $wp_version;
        $version_info=get_transient('aoisora-update-'.$this->slug);
        if($version_info)
            return $version_info;
        $body=array('id' => $this->slug);
        if($this->update_site_extra)
            $body=$body+$this->update_site_extra;

        $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
        $options['headers']= array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
            'Content-Length' => strlen(implode(',',$body)),
            'user-agent' => 'WordPress/' . $wp_version,
            'referer'=> get_bloginfo('url')
        );
        $raw_response = wp_remote_post($this->update_site, $options);
        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)){
            $data=unserialize($raw_response['body']);
            set_transient('aoisora-update-'.$this->slug,$data,60*60*2 );
            return $data;
        }
        return array();
    }
    static $count=0;

    /**
     *
     */
    function transientUpdatePlugins(){
        if(empty($this->update_site) || !is_admin())
            return;
        $plugins = get_transient("update_plugins");
        $plugins = $this->siteTransientUpdatePlugins($plugins);
        set_transient("update_plugins", $plugins);
        if(function_exists("set_site_transient"))
            set_site_transient("update_plugins", $plugins);
    }

    /**
     * @param bool $plugins
     * @return bool
     */
    function siteTransientUpdatePlugins($plugins=false){
        if(empty($this->UPDATE_SITE) || !is_admin())
            return false;
        $plugin=$this->application->getInstallName();
        $version_info = $this->getVersionInfo();

        if(!$version_info["has_access"] || version_compare($this->version, $version_info["version"], '>=')){
            if(isset($plugins->response[$plugin]))
                unset($plugins->response[$plugin]);
            return $plugins;
        }
        $package=$version_info['url'];
        if($this->update_site_extra)
            foreach($this->update_site_extra as $key => $value)
                $package=str_replace('{'.$key.'}',urlencode($value),$package);
        $update_data = new \stdClass();
        $update_data->slug = $this->application->getName();
        $update_data->new_version = $version_info['version'];
        $update_data->url = $version_info['site'];
        $update_data->package = $package;
        $plugins->response[$plugin] = $update_data;
        return $plugins;
    }
}