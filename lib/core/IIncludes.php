<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 22:58
 */
interface IIncludes
{
    function register( $handle, $src, $deps, $ver, $in_footer );
    function deregister($handle);
    function enqueue($handle, $src, $deps, $ver, $in_footer);
    function deenqueue($handle);
    function init();
}
