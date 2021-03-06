<?php
/*
Plugin Name: CloudLessMVC
Plugin URI: http://cloudlessmvc.org
Description: CloudLessMVC is a PHP MVC Framework (for WordPress).
Version: 14.3
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora
namespace CLMVC;

use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Core\Includes\ScriptIncludes;
use CLMVC\Core\Includes\StyleIncludes;

include('init.php');

    /**
     * Class AoiSora
     * WordPress plugin that sets up the CloudLessMVC framework
     */
class AoiSora extends Core\Application\ApplicationBase {
    /**
     * @var $options Core\Options
     */
    public $options;
    private static $instance;

    /**
     * Sets up aoisoraLoaded hook, calls parent class. Setups up standard libraries
     */
    public function __construct(){
        parent::__construct('AoiSora',sl_file('AoiSora'),true, true);
        $this->setFrontIncludes();
    }

    /**
     * Setup the environment
     */
    public function onInit() {
    }

    /**
     *
     */
    public function onAfterInit() {
        add_action('plugins_loaded', array($this, 'loaded'));
    }

    /**
     * Initiates options for the plugin
     */
    public function onLoadOptions() {
    }

    /**
     * Configures standard JS libraries etc.
     */
    private function setFrontIncludes() {
        ScriptIncludes::instance()
            ->register(new FrontInclude('superagent', clmvc_app_url('AoiSora', '/lib/js/superagent/superagent.js')))
            ->register(new FrontInclude('jquery-ui-stars', clmvc_app_url('AoiSora', '/lib/js/jquery.ui.stars/ui.stars.min.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-widget')))
            ->register(new FrontInclude('jquery-ui-tag-it', clmvc_app_url('AoiSora', '/lib/js/jquery.ui.tag-it/ui.tag-it.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-widget')));
        ScriptIncludes::instance()->register(new FrontInclude('jquery-ui-stars', clmvc_app_url('AoiSora', '/lib/js/jquery.ui.stars/ui.stars.min.css')));
        StyleIncludes::instance()
            ->register(new FrontInclude('forms', clmvc_app_url('AoiSora', '/lib/css/forms.css')))
            ->register(new FrontInclude('wordpress', clmvc_app_url('AoiSora', '/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css')));
        add_action('wp_print_scripts', function() {?>
            <script>
                var includesUrl = '<?php echo includes_url() ?>';
            </script>
        <?php
        });
    }

    /**
     * Instantiates the class
     * @return AoiSora
     */
    public static function instance(){
        if(self::$instance)
            return self::$instance;
        self::$instance=new AoiSora();
        self::$instance->init();
        return self::$instance;
    }

    /**
     * Called on plugins loaded. runs hooks. aoisora-libraries, aoisora-loaded
     */
    public function loaded() {
        Events\Hook::run('cloudless-libraries');
        Events\Hook::run('cloudless-loaded');
    }
}
AoiSora::instance();
