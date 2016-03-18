<?php
/**
 * Loads functions
 */
require __DIR__.'/lib/Helpers/ArraysHelper.php';
require __DIR__.'/lib/Core/Security/SecurityHelpers.php';

/**
 * Checks if page is being viewed by a mobile.
 * @return bool
 */
function clmvc_is_mobile() {
    $mobile_detect=\CLMVC\Core\Container::instance()->fetchOrMake(\CLMVC\Helpers\MobileDetect::class);
    return $mobile_detect->isMobile() && !clmvc_is_tablet();
}

/**
 * Checks if page is being viewed by a tablet.
 * @return bool
 */
function clmvc_is_tablet() {
    $mobile_detect=\CLMVC\Core\Container::instance()->fetchOrMake(\CLMVC\Helpers\MobileDetect::class);
    return $mobile_detect->isTablet();
}

/**
 * Returns plugin path
 * @param string $file
 * @return string
 */
function sl_file($file, $isPlugin = true)
{
    if ($isPlugin) {
        return CLOUDLESS_APP_DIR.'/'.$file.'/'.$file.'.php';
    }

    return CLOUDLESS_APP_DIR.'/'.$file;
}