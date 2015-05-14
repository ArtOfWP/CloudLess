<?php

namespace CLMVC\ViewEngines\WordPress;

class WpHelper
{
    public static function registerSettings($optiongroup, $optionkeys)
    {
        foreach ($optionkeys as $key) {
            register_setting($optiongroup, $key);
        }
    }
    public static function registerStyle($handle, $src = false, $deps = false, $ver = false, $media = false)
    {
        wp_register_style($handle, $src, $deps, $ver, $media);
    }
    public static function enqueueStyle($handle, $src = false, $deps = false, $ver = false, $media = false)
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }
    public static function registerScript($handle, $src = false, $deps = false, $ver = false, $in_footer = false)
    {
        wp_register_style($handle, $src, $deps, $ver, $in_footer);
    }
    public static function enqueueScript($handle, $src = false, $deps = false, $ver = false, $in_footer = false)
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $in_footer);
    }
}
