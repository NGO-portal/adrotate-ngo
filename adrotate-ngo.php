<?php
/*
* Plugin Name: AdRotate-NGO
* Plugin URI: https://ngo-portal.org
* Description: Shows ads on sites that are set up on the network site in a WPMU installation. Meant to show ads on NGO-Protal. Requires AdRotate active on the portal site. This is a stripped version meant to sho ads on sites where the network site has an active installation of AdRotate. There are no settings in this plugin. Add the ads on the network site and add hooks in your theme for your sites. Only tested with ad-GROUPS that shows (random if you have several ads per group) single ads.
* Version: 1.1
* Author: George Bredberg
* Author URI: https://datagaraget.se
* Text Domain: adrotate-ngo
* Domain Path: /languages/
* License: GPLv3
* GitHub Plugin URI: https://github.com/NGO-portal/adrotate-ngo
*/

/*
To update, copy the files you find in AdRotate-NGO from the updated AdRotate, and then search and replace all instances of $wpdb->prefix with $wpdb->base_prefix. Keep this file.
What that does is to make sure that AdRotate-NGO will get the ads from the portal site instead of from the active site. For clarity, rename adrotate.php to something else, like adrotate-ngo and give some explanation in the plugin definition. It's a bit of a hack of course, but it works, it's relatively easy to update and it's free ;)
This should work with showing single ads to, but to allow automatic change of ads it's better to place the ads in a group, even if you show only an ad at a time per place. I doubt that widgets will work, but it's not tested, so if you feel up to it, please do try.
If you enable this plugin at the same site as AdRotate your site will start throwing errors at you ;)
Since this is a stripped copy of AdRotate the functions are named the same as in AdRotate...
All the credits go to Arnan that made the AdRotate plugin.
*/

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Load translation
add_action( 'plugins_loaded', 'arngo_load_plugin_textdomain' );
function arngo_load_plugin_textdomain() {
	load_plugin_textdomain( 'adrotate-ngo', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Get site id
$blog_id = get_current_blog_id();

// Check if mother-plugin is installed and activated on network site. If not, deactivate this plugin and print an error message.
if( is_main_site( $blog_id ) ) {
	add_action( 'admin_init', 'arngo_plugin_has_parent_plugin' );
}

function arngo_plugin_has_parent_plugin() {
	$req_plugin = 'adrotate/adrotate.php';
	if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( $req_plugin ) ) {
		add_action( 'admin_notices', 'arngo_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

function arngo_plugin_notice(){
	?><div class="error"><p><?php _e( 'AdRotate-NGO requires that the plugin "AdRotate" is installed and activated on the network site before you activate it. If you deactivate AdRotate, this plugin will get deactivated to.', 'adrotate-ngo' ); ?></p></div><?php
}

// Load stripped and hacked version of AdRotate if we are on a NGO-site
if( ! is_main_site( $blog_id ) ) {
	require_once('adrotate.php');
}

//////////////////* Clean up WP-admin menu*/////////////////////
if(!function_exists('wp_get_current_user')) { require_once(ABSPATH . "wp-includes/pluggable.php"); }
/* Check if AdRotator is active. */
if ( function_exists( 'adrotate_dashboard' ) ) {
	add_action( 'wp_loaded', 'arngo_cleanup' ); // Delay user check till wp is fully loaded
	add_action( 'admin_menu', 'ngo_remove_ar_submenu_pages' );
	add_action('widgets_init', 'ngo_unregister_ar_widgets', 11);
}

function arngo_cleanup() {
	// Remove entire adrotate menu if not superadmin
	if( ! is_super_admin() ){
		add_action( 'admin_menu', 'ngo_remove_ar_menu_page' );
	}
}

// Remove entire adrotate menu if not on main site
if( ! is_main_site( $blog_id ) ) {
	add_action( 'admin_menu', 'ngo_remove_ar_menu_page' );
}

function ngo_remove_ar_menu_page(){
	remove_menu_page( 'adrotate' );
}

function ngo_remove_ar_submenu_pages() {
	remove_submenu_page( 'adrotate', 'adrotate' ); // Info-page
	remove_submenu_page( 'adrotate', 'adrotate-pro' ); // adrotate-pro
	remove_submenu_page( 'adrotate', 'adrotate-media' ); // Manage media, pro feature
//	remove_submenu_page( 'adrotate', 'adrotate-settings' ); // Settings
}

function ngo_unregister_ar_widgets() {
	unregister_widget('adrotate_widgets');
}

?>
