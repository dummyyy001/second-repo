<?php
/**
 * Plugin Name: Kadence Conversions - Popups, slide-ins
 * Description: Create popups and slide ins that generate leads. Convert your views to an engaged audience of followers, customers and promoters.
 * Version: 1.1.0
 * Author: Kadence WP
 * Author URI: http://kadencewp.com/
 * Requires PHP: 7.2
 * License: GPLv2 or later
 * Text Domain: kadence-conversions
 *
 * @package Kadence Conversions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KADENCE_CONVERSIONS_PATH', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR );
define( 'KADENCE_CONVERSIONS_URL', plugin_dir_url( __FILE__ ) );
define( 'KADENCE_CONVERSIONS_FILE', __FILE__ );
define( 'KADENCE_CONVERSIONS_VERSION', '1.1.0' );

require_once KADENCE_CONVERSIONS_PATH . 'vendor/vendor-prefixed/autoload.php';
require_once KADENCE_CONVERSIONS_PATH . 'vendor/autoload.php';
require_once KADENCE_CONVERSIONS_PATH . 'inc/uplink/Helper.php';
require_once KADENCE_CONVERSIONS_PATH . 'inc/uplink/Connect.php';

require_once KADENCE_CONVERSIONS_PATH . 'inc/class-kadence-conversions-setup.php';
/**
 * Load Plugin
 */
function kadence_conversions_init() {
	require_once KADENCE_CONVERSIONS_PATH . 'class-kadence-conversions.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-conversions-duplicate-post.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-conversions-post-type-controller.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/post-select-rest-controller.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-kadence-conversions-settings.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-minified-css.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-conversions-frontend.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-analytics-dashboard-util.php';
	require_once KADENCE_CONVERSIONS_PATH . 'inc/class-analytics-ajax.php';
}
add_action( 'plugins_loaded', 'kadence_conversions_init' );

/**
 * Load the plugin textdomain
 */
function kadence_conversions_lang() {
	load_plugin_textdomain( 'kadence-cloud', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'kadence_conversions_lang' );

