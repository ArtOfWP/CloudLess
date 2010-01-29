<?php
define('ROOT','../../../');
require(ROOT.'wp-load.php');
wp_register_style('forms',plugins_url('AoiSora/lib/css/forms.css'));			
wp_register_style('wordpress',plugins_url('AoiSora/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));			

wp_enqueue_style('wordpress');
wp_enqueue_style('forms');
wp_enqueue_style('wpaffshopadmin');

//include('../../../wp-admin/index.php');
require(ROOT.'wp-admin/admin.php');
?>
<?php
/**
 * WordPress Clean Template
 */

//@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

$title = esc_html( strip_tags( $_GET['title'] ) );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php //do_action('admin_xml_ns'); ?> <?php //language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php //bloginfo('html_type'); ?>; charset=<?php //echo get_option('blog_charset'); ?>" />
<title><?php echo $title; ?></title>
<?php

wp_admin_css( 'css/global' );
wp_admin_css();
wp_admin_css( 'css/colors' );
wp_admin_css( 'css/ie' );
wp_enqueue_script('utils');
$pagenow="page.php";
$hook_suffix = '';
if ( isset($page_hook) )
	$hook_suffix = "$page_hook";
else if ( isset($plugin_page) )
	$hook_suffix = "$plugin_page";
else if ( isset($pagenow) )
	$hook_suffix = "$pagenow";

$admin_body_class = preg_replace('/[^a-z0-9_-]+/i', '-', $hook_suffix);
?>
<?php 

if ( in_array( $pagenow, array('post.php', 'post-new.php', 'page.php', 'page-new.php') ) ) {
	add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
	wp_enqueue_script('quicktags');
}
do_action('admin_enqueue_scripts', $hook_suffix);
do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');

if ( get_user_setting('mfold') == 'f' ) {
	$admin_body_class .= ' folded';
}

if ( $is_iphone ) { ?>
<style type="text/css">.row-actions{visibility:visible;}</style>
<?php } ?>
<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js"></script>

</head>
<body class="wp-admin no-js <?php echo apply_filters( 'admin_body_class', '' ) . " $admin_body_class"; ?>">
<script type="text/javascript">
//<![CDATA[
(function(){
var c = document.body.className;
c = c.replace(/no-js/, 'js');
document.body.className = c;
})();
//]]>
</script>

<div id="wpwrap">
<div id="wpcontent">
<div id="wphead">
<h1 id="site-heading"><span id="site-title" style="color:#fff">Add new item</span></h1>
<div id="wphead-info">
<div id="user_info">
</div>
</div>
</div>
<div id="wpbody">
<div id="wpbody-content" style="margin-left:-150px">
<?php 
Route::reroute();
echo BaseController::ViewContents(); ?>
<div class="clear"></div>
</div><!-- wpbody-content -->
<div class="clear"></div>
</div><!-- wpbody -->
<div class="clear"></div>
</div><!-- wpcontent -->
</div><!-- wpwrap -->

<div id="footer">
<p id="footer-left" class="alignleft">
</p>
<div class="clear"></div>
</div>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
<?php ?>
<?php wp_print_scripts() ?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time() ?>'};
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = '<?php echo substr($pagenow, 0, -4); ?>', adminpage = '<?php echo $admin_body_class; ?>';
//]]>
</script>
</body>
</html>
