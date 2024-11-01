<?php
/**
 *
 * @package   WP Content Permissions
 * @author    oren@sogo.co.il (SOGO)
 * @license   GPL-2.0+
 * @link      http://sogo.co.il
 * @copyright 2014 sogo
 *
 * @wordpress-plugin
 * Plugin Name:       WP Content Permissions
 * Plugin URI:        http://plugins.ohav.co.il/WP_Content_Permissions
 * Description:       Allow site admin to display different content for login and guest users
 * Version:           1.2
 * Author:            OHAV
 * Author URI:        http://ohav.co.il
 * Text Domain:       oh
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * - replace `WP_Content_Permission.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/wp-content-permission.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *

 *
 * - replace WP_Content_Permission with the name of the class defined in
 *   `WP_Content_Permission.php`
 */
register_activation_hook( __FILE__, array( 'wp_content_permission', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'wp_content_permission', 'deactivate' ) );

/*

 *
 * - replace WP_Content_Permission with the name of the class defined in
 *   `WP_Content_Permission.php`
 */
add_action( 'plugins_loaded', array( 'wp_content_permission', 'get_instance' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-admin.php` with the name of the plugin's admin file
 * - replace WP_Content_Permission_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-content-permission-admin.php' );
	//require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/ohmem-settings.php' );
	add_action( 'plugins_loaded', array( 'WP_Content_Permission_Admin', 'get_instance' ) );

}
