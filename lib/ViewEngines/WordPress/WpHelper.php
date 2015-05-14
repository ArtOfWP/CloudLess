<?php

namespace CLMVC\ViewEngines\WordPress;

class WpHelper
{
    /**
     * @param $optiongroup
     * @param $optionkeys
     */
    public static function registerSettings($optiongroup, $optionkeys)
    {
        foreach ($optionkeys as $key) {
            register_setting($optiongroup, $key);
        }
    }

    /**
     * @param $handle
     * @param string|bool $src
     * @param array|bool $deps
     * @param string|bool $ver
     * @param string|bool $media
     */
    public static function registerStyle($handle, $src = false, $deps = false, $ver = false, $media = false)
    {
        wp_register_style($handle, $src, $deps, $ver, $media);
    }
    /**
     * @param $handle
     * @param string|bool $src
     * @param array|bool $deps
     * @param string|bool $ver
     * @param string|bool $media
     */
    public static function enqueueStyle($handle, $src = false, $deps = false, $ver = false, $media = false)
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }
    /**
     * @param $handle
     * @param string|bool $src
     * @param array|bool $deps
     * @param string|bool $ver
     * @param string|bool $in_footer
     */
    public static function registerScript($handle, $src = false, $deps = false, $ver = false, $in_footer = false)
    {
        wp_register_style($handle, $src, $deps, $ver, $in_footer);
    }
    /**
     * @param $handle
     * @param string|bool $src
     * @param array|bool $deps
     * @param string|bool $ver
     * @param string|bool $in_footer
     */
    public static function enqueueScript($handle, $src = false, $deps = false, $ver = false, $in_footer = false)
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $in_footer);
    }
}
