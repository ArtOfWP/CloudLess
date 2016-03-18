<?php
namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Events\Filter;
use CLMVC\Events\Hook;
use CLMVC\Events\View;
use CLMVC\Views\Shortcode;

/**
 * Class WpActionOverrides
 * @package CLMVC\ViewEngines\WordPress
 */
class WpActionOverrides {
    private $hooks = array('init', 'admin_init', 'admin_menu', 'set_plugin_has_updates' => 'update_option__transient_update_plugins', 'template_redirect');
    private $view_sections = array('print_styles' => 'wp_print_styles', 'print_scripts' => 'wp_print_scripts', 'admin_print_scripts', 'admin_print_styles', 'footer' => 'wp_footer', 'head' => 'wp_head', 'admin_head', 'admin_footer', 'wp_print_scripts', 'wp_footer', 'wp_print_styles', 'enqueue_scripts' => 'wp_enqueue_scripts');
    private $filters = array('query_vars', 'http_request_args', 'rewrite_rules_array', 'list_pages', 'rewrite_rules_array', 'rewrite_rules_array', 'set_plugin_has_updates' => 'pre_set_site_transient_update_plugins', 'template_include', 'after_plugin_row','document_title_parts', 'wp_title', 'status_header', 'pre_handle_404', 'do_parse_request', 'posts_request');

    public function init() {
        Shortcode::registerHandler('add_shortcode');
        $this->actions();
        $this->viewSections();
        $this->filters();
        add_action('wp_register_scripts', function () {
            Hook::run('scripts-register');
        });
        add_action('wp_register_style', function () {
            Hook::run('style-register');
        });

    }

    private function actions() {
        foreach ($this->hooks as $key => $hook) {
            if (is_numeric($key)) {
                Hook::registerHandler($hook, [$this, 'wp_hook_handler']);
            } else {
                Hook::registerHandler($key, [$this, 'wp_hook_handler']);
            }
        }
    }

    private function viewSections() {
        foreach ($this->view_sections as $key => $section) {
            if (is_numeric($key)) {
                View::registerHandler($section, [$this, 'wp_section_handler']);
            } else {
                View::registerHandler($key, [$this, 'wp_section_handler']);
            }
        }
    }

    private function filters() {
        foreach ($this->filters as $key => $filter) {
            if (is_numeric($key)) {
                Filter::registerHandler($filter, [$this, 'wp_filter_handler']);
            } else {
                Filter::registerHandler($key, [$this, 'wp_filter_handler']);
            }
        }
    }

    public function wp_hook_handler($hook, $callback, $priority = 100, $params = 1) {
        $new_hook = array_key_exists_v($hook, $this->hooks);
        if ($new_hook) {
            add_action($new_hook, $callback, $priority, $params);
        } else {
            add_action($hook, $callback, $priority, $params);
        }
    }

    public function wp_filter_handler($filter, $callback, $priority = 100, $params = 1) {
        $new_filter = array_key_exists_v($filter, $this->filters);
        if ($new_filter) {
            add_action($new_filter, $callback, $priority, $params);
        } else {
            add_action($filter, $callback, $priority, $params);
        }
    }

    public function wp_section_handler($section, $callback, $priority = 100, $params = 1) {
        $new_section = array_key_exists_v($section, $this->view_sections);
        if ($new_section) {
            add_action($new_section, $callback, $priority, $params);
        } else {
            add_action($section, $callback, $priority, $params);
        }
    }

    public function wp_section_handler_run($section, $params = array()) {
        $section = (array_key_exists_v($section, $this->view_sections)) ? : $section;
        if (!empty($params)) {
            call_user_func_array('do_action', array($section, $params));
        } else {
            call_user_func_array('do_action', array($section));
        }
    }
}