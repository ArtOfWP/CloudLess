<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Core\AoiSoraSettings;
use CLMVC\Core\Debug;
use CLMVC\Core\Option;
use CLMVC\Core\Options;
use CLMVC\Events\Filter;
use CLMVC\Events\Hook;
use CLMVC\Events\View;
use CLMVC\Helpers\Http;

/**
 * Class WpApplicationBase
 * Todo: make it independant of WordPress. Make an applciation base and then make an DI for WordPress.
 */
class WpApplicationBase
{
    protected $installFromPath;
    protected $VERSION = false;
    protected $VERSION_INFO_LINK = false;
    protected $UPDATE_SITE = false;
    protected $UPDATE_SITE_EXTRA = false;
    protected $SLUG = false;
    private $models = array();
    public $pluginName;
    public $dir;
    public $app;
    public $options;
    private $useInstall;
    private $useOptions;

    /**
     * @param $appName
     * @param $file
     * @param bool $useOptions
     * @param bool $useInstall
     * @param bool $basename
     */
    public function WpApplicationBase($appName, $file, $useOptions = false, $useInstall = false, $basename = false)
    {
        add_action('plugins_loaded', array($this, 'aoisoraLoaded'));
        $this->dir = dirname($file);
        $this->app = $appName;
        if ($basename) {
            $this->pluginName = $basename;
        }//"$appName/$appName.php";
        else {
            $this->pluginName = plugin_basename($file);
        }//"$appName/$appName.php";
        register_activation_hook($file, array(&$this, 'activate'));
        register_deactivation_hook($file, array(&$this, 'deactivate'));
        $this->installFromPath = dirname($file).'/app/Core/domain/';
        $this->useInstall = $useInstall;
        $this->useOptions = $useOptions;
        if (method_exists($this, 'onRegisterQueryVars')) {
            Filter::register('query_vars', array(&$this, 'registerQueryVars'));
        }
        if (method_exists($this, 'on_init')) {
            Hook::register('init', array(&$this, 'on_init'));
        }
        if (method_exists($this, 'onInit')) {
            Hook::register('init', array(&$this, 'onInit'));
        }
        if (is_admin()) {
            if (method_exists($this, 'onInitAdmin')) {
                Hook::register('init', array(&$this, 'onInitAdmin'));
            }
            if (method_exists($this, 'onAdminInit')) {
                Hook::register('admin_init', array(&$this, 'onAdminInit'));
            }
            if (method_exists($this, 'onAdminMenu')) {
                Hook::register('admin_menu', array(&$this, 'onAdminMenu'));
            }
            if (method_exists($this, 'onPrintAdminScripts') || method_exists($this, 'on_print_admin_scripts')) {
                Hook::register('init', array(&$this, 'printAdminScripts'));
            }
            if (method_exists($this, 'onPrintAdminStyles') || method_exists($this, 'on_print_admin_styles')) {
                Hook::register('init', array(&$this, 'printAdminStyles'));
            }

            Hook::register('admin_init', array(&$this, 'registerSettings'));

            Filter::registerHandler('plugin_action_links_'.$this->pluginName, 'wp_filter_handler');
            if (method_exists($this, 'onPluginPageLink')) {
                Filter::register('plugin_action_links_'.$this->pluginName, array(&$this, 'pluginPageLinks'), 10, 2);
            }

            if (method_exists($this, 'onPluginRowMessage') || method_exists($this, 'on_plugin_row_message')) {
                Hook::registerHandler('after_plugin_row_'.$this->pluginName, 'wp_section_handler');
                Hook::register('after_plugin_row_'.$this->pluginName, array(&$this, 'afterPluginRow'), 10, 2);
            }
            if (method_exists($this, 'onRewriteRulesArray')) {
                Filter::register('rewrite_rules_array', array(&$this, 'onRewriteRulesArray'));
            }
            if (isset($_GET['plugin']) && $_GET['plugin'] == $appName) {
                Hook::register('install_plugins_pre_plugin-information', array(&$this, 'versionInformation'));
            }
        }
        if (method_exists($this, 'onPrintStyles')) {
            View::register('print_styles', array(&$this, 'printStyles'));
        }
        if (method_exists($this, 'onPrintScripts')) {
            View::register('print_scripts', array(&$this, 'printScripts'));
        }
        if (method_exists($this, 'onAddPageLinks')) {
            Filter::register('list_pages', array(&$this, 'onAddPageLinks'));
        }
        if (method_exists($this, 'onRewriteRulesArray')) {
            Filter::register('rewrite_rules_array', array(&$this, 'onRewriteRulesArray'));
        }
        if (method_exists($this, 'onRenderFooter')) {
            View::register('footer', array(&$this, 'onRenderFooter'));
        }
        Filter::register('set_plugin_has_updates', array(&$this, 'siteTransientUpdatePlugins'));
        Hook::register('set_plugin_has_updates', array(&$this, 'transientUpdatePlugins'));
    }

    /**
     *
     */
    public function init()
    {
        if (method_exists($this, 'onInitialize')) {
            $this->onInitialize();
        }
        if (is_admin() && (method_exists($this, 'onInitUpdate'))) {
            if (method_exists($this, 'onInitUpdate')) {
                $this->onInitUpdate();
            }
            $oldVersion = AoiSoraSettings::getApplicationVersion($this->app);
            if ($this->installed() && version_compare($oldVersion, $this->VERSION, '<')) {
                AoiSoraSettings::addApplication($this->app, $this->dir, $this->VERSION);
                $this->update();
            }
            if ($this->UPDATE_SITE && isset($_REQUEST['action']) && 'upgrade-plugin' == $_REQUEST['action'] && isset($_REQUEST['plugin']) && urldecode($_REQUEST['plugin']) == $this->pluginName) {
                Filter::register('http_request_args', array(&$this, 'addUpdateUrl'), 10, 2);
            }
        }
    }

    /**
     * @param $r
     * @param $url
     *
     * @return mixed
     */
    public function addUpdateUrl($r, $url)
    {
        $r['headers']['Referer'] = get_site_url();

        return $r;
    }

    /**
     * @param $public_query_vars
     *
     * @return array
     */
    public function registerQueryVars($public_query_vars)
    {
        $vars = array();
        if (method_exists($this, 'onRegisterQueryVars')) {
            $vars = $this->onRegisterQueryVars();
        }
        foreach ($vars as $var) {
            $public_query_vars[] = $var;
        }

        return $public_query_vars;
    }

    /**
     *
     */
    public function registerSettings()
    {
        if (method_exists($this, 'on_register_settings') || method_exists($this, 'onRegisterSettings')) {
            if (method_exists($this, 'onRegisterSettings')) {
                $settings = $this->onRegisterSettings();
                foreach ($settings as $option => $key) {
                    if (is_array($key)) {
                        WpHelper::registerSettings($option, $key);
                    } else {
                        WpHelper::registerSettings($option, array($key));
                    }
                }
            }
        }
        if ($this->useOptions) {
            WpHelper::registerSettings($this->app, array($this->app));
        }
    }

    /**
     * @param $plugin_file
     * @param $plugin_data
     */
    public function afterPluginRow($plugin_file, $plugin_data)
    {
        if (method_exists($this, 'onPluginRowMessage')) {
            $display = $this->onPluginRowMessage();
            /*
             * @var $trclass
             * @var $trstyle
             * @var $tdclass
             * @var $tdstyle
             * @var $divclass
             * @var $divstyle
             * @var $message
             */
            extract($display);
            echo '<tr class="',$trclass,'" style="',$trstyle,'"><td colspan="3" class="',$tdclass,'" style="',$tdstyle,'"><div class="',$divclass,'" style="',$divstyle,'">',$message,'</div></td></tr>';
        }
    }

    /**
     * @param $links
     *
     * @return mixed
     */
    public function pluginPageLinks($links)
    {
        if (method_exists($this, 'onPluginPageLink')) {
            $plugin_link = $this->onPluginPageLink();
        }
        array_unshift($links, $plugin_link); // before other links
        return $links;
    }

    /**
     *
     */
    public function activate()
    {
        if (method_exists($this, 'onInitUpdate')) {
            $this->onInitUpdate();
        }
        $oldVersion = AoiSoraSettings::getApplicationVersion($this->app);
        $installed = $this->installed();
        AoiSoraSettings::addApplication($this->app, $this->dir, $this->VERSION);
        if ($oldVersion && $installed && version_compare($oldVersion, $this->VERSION, '<')) {
            $this->update();
        } elseif (!$this->useInstall) {
            AoiSoraSettings::installApplication($this->app);
        }
        if (!$installed && $this->useOptions) {
            //			$this->options= Option::create($this->app);
            $this->options = new Options($this->app);
            if (method_exists($this, 'on_load_options')) {
                $this->on_load_options();
            }
            if (method_exists($this, 'onLoadOptions')) {
                $this->onLoadOptions();
            }
        }
        if (method_exists($this, 'onActivate')) {
            $this->onActivate();
        }
    }

    /**
     *
     */
    public function deactivate()
    {
        if (method_exists($this, 'onDeactivate')) {
            $this->onDeactivate();
        }
        if (!$this->useInstall) {
            AoiSoraSettings::uninstallApplication($this->app);
            AoiSoraSettings::removeApplication($this->app);
        }
    }

    /**
     * @return bool
     */
    public function installed()
    {
        return AoiSoraSettings::installed($this->app);
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (method_exists($this, 'onPreInstall')) {
            $this->onPreInstall();
        }
        $this->models = array();
        $this->load($this->installFromPath);
        $result = true;
        $this->create();
        if ($result) {
            AoiSoraSettings::installApplication($this->app);
        }
        if (method_exists($this, 'onAfterInstall')) {
            $this->onAfterInstall();
        }

        return $result;
    }

    /**
     *
     */
    private function create()
    {
        global $db;
        foreach ($this->models as $model) {
            $m = new $model();
            $db->createTable($m);
        }
        $db->createStoredRelations();
        $db->createStoredIndexes();
    }

    /**
     *
     */
    public function uninstall()
    {
        if (method_exists($this, 'onPreUninstall')) {
            $this->onPreUninstall();
        }
        $this->models = array();
        $this->load($this->installFromPath);
        $result = true;
        $this->drop();
        if ($result) {
            AoiSoraSettings::uninstallApplication($this->app);
        }
        if ($this->useOptions) {
            $this->options = Option::create($this->app);
            $this->options->delete();
        }
        AoiSoraSettings::removeApplication($this->app);
        if (method_exists($this, 'onAfterUninstall')) {
            $this->onAfterUninstall();
        }
    }

    /**
     *
     */
    private function drop()
    {
        global $db;
        foreach ($this->models as $model) {
            $m = new $model();
            $db->dropTable($m);
        }
        $db->dropStoredRelations();
    }

    /**
     *
     */
    public function delete()
    {
        if ($this->options) {
            $this->options->delete();
        }
    }

    /**
     *
     */
    private function update()
    {
        if (method_exists($this, 'onUpdate')) {
            $this->onUpdate();
        }
        $updatePath = trim($this->dir, '/').'/app/updates/'.$this->VERSION.'.php';
        if (file_exists('/'.$updatePath)) {
            include '/'.$updatePath;
        } elseif (file_exists($updatePath)) {
            include $updatePath;
        }
    }

    /**
     * @param string $dir
     */
    private function load($dir)
    {
        Debug::Value('Loading directory', $dir);
        $handle = opendir($dir);
        while (false !== ($resource = readdir($handle))) {
            if ($resource != '.' && $resource != '..') {
                if (is_dir($dir.$resource)) {
                    $this->load($dir.$resource.'/');
                    continue;
                }
                $this->models[] = str_replace('.php', '', $resource);
                Debug::Value('Loaded', $resource);
            }
        }
        closedir($handle);
    }

    /**
     * @param $styles
     */
    private function loadstyles($styles)
    {
        if (isset($styles) && !empty($styles) && is_array($styles)) {
            foreach ($styles as $name => $file) {
                if (is_string($name)) {
                    $myStyleUrl = WP_PLUGIN_URL.'/'.$this->app.$file;
                    $myStyleFile = WP_PLUGIN_DIR.'/'.$this->app.$file;
                    if (file_exists($myStyleFile)) {
                        WpHelper::registerStyle($name, $myStyleUrl);
                        WpHelper::enqueueStyle($name);
                    }
                } else {
                    WpHelper::enqueueStyle($file);
                }
            }
        }
    }

    /**
     * @param $scripts
     */
    private function loadscripts($scripts)
    {
        if (isset($scripts) && !empty($scripts) && is_array($scripts)) {
            foreach ($scripts as $name => $file) {
                if (is_string($name)) {
                    $myScriptUrl = WP_PLUGIN_URL.'/'.$this->app.$file;
                    $myScriptFile = WP_PLUGIN_DIR.'/'.$this->app.$file;
                    if (file_exists($myScriptFile)) {
                        WpHelper::registerScript($name, $myScriptUrl);
                        WpHelper::enqueueScript($name);
                    }
                } else {
                    WpHelper::enqueueScript($file);
                }
            }
        }
    }

    /**
     *
     */
    public function printAdminStyles()
    {
        $this->loadstyles($this->on_admin_print_styles());
    }

    /**
     *
     */
    public function printAdminScripts()
    {
        $this->loadscripts($this->on_admin_print_scripts());
    }

    /**
     *
     */
    public function printScripts()
    {
        $this->loadscripts($this->on_print_scripts());
    }

    /**
     *
     */
    public function printStyles()
    {
        $this->loadstyles($this->on_print_styles());
    }

    /**
     * @return array|mixed
     */
    public function getVersionInfo()
    {
        global $wp_version;
        $version_info = get_transient('aoisora-update-'.$this->SLUG);
        if ($version_info) {
            return $version_info;
        }
        $body = array('id' => $this->SLUG);
        if ($this->UPDATE_SITE_EXTRA) {
            $body = $body + $this->UPDATE_SITE_EXTRA;
        }

        $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
        $options['headers'] = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset='.get_option('blog_charset'),
            'Content-Length' => strlen(implode(',', $body)),
            'user-agent' => 'WordPress/'.$wp_version,
            'referer' => get_bloginfo('url'),
        );
        $raw_response = wp_remote_post($this->UPDATE_SITE, $options);
        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
            $data = unserialize($raw_response['body']);
            set_transient('aoisora-update-'.$this->SLUG, $data, 60 * 60 * 2);

            return $data;
        }

        return array();
    }
    public static $count = 0;

    /**
     *
     */
    public function transientUpdatePlugins()
    {
        if (empty($this->UPDATE_SITE) || !is_admin()) {
            return;
        }
        $plugins = get_transient('update_plugins');
        $plugins = $this->siteTransientUpdatePlugins($plugins);
        set_transient('update_plugins', $plugins);
        if (function_exists('set_site_transient')) {
            set_site_transient('update_plugins', $plugins);
        }
    }

    /**
     * @param $plugins
     *
     * @return bool
     *//*
    function siteTransientUpdatePlugins($plugins=null){
        if(empty($this->UPDATE_SITE) || !is_admin())
            return false;
        global $wp_version;
        $plugin=$this->pluginName;
        $version_info = $this->getVersionInfo();

        if(!$version_info["has_access"] || version_compare($this->VERSION, $version_info["version"], '>=')){
            if(isset($plugins->response[$plugin]))
                unset($plugins->response[$plugin]);
        }else{
            $package=$version_info['url'];
            if($this->UPDATE_SITE_EXTRA)
                foreach($this->UPDATE_SITE_EXTRA as $key => $value)
                    $package=str_replace('{'.$key.'}',urlencode($value),$package);
            $update_data = new stdClass();
            $update_data->slug = $this->app;
            $update_data->new_version = $version_info['version'];
            $update_data->url = $version_info['site'];
            $update_data->package = $package;
            $plugins->response[$plugin] = $update_data;
        }
        return $plugins;
    }*/

    /**
     *
     */
    public function versionInformation()
    {
        if (!$this->VERSION_INFO_LINK) {
            return;
        }
        $response = Http::getPage($this->VERSION_INFO_LINK.'&id='.$this->SLUG);
        wp_die(nl2br($response));
        exit;
    }
}
