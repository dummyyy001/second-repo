<?php
/**
 * Plugin Name: Gift Up
 * Plugin URI: https://www.giftup.com/
 * Description: The simplest way to sell your own gift cards/certificates/vouchers from inside your WordPress website easily with no monthly fee. If you also use WooCommerce, the gift cards are redeemable in your website shopping cart.
 * Version: 2.25
 * Author: Gift Up
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Developer: Gift Up
 * Developer URI: https://www.giftup.com/
 * Author URI: https://www.giftup.com/
 * WC requires at least: 3.2.0
 * WC tested up to: 8.8.2
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

define( 'GIFTUP_VERSION', '2.25' );
define( 'GIFTUP_SESSION_KEY', 'giftup_gift_card_code' );
define( 'GIFTUP_ORDER_META_CODE_KEY', '_giftup_code' );
define( 'GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY', '_giftup_requested_balance' );
define( 'GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY', '_giftup_redeemed_balance' );

require_once plugin_dir_path( __FILE__ ) . 'view/giftup-checkout.php';
require_once plugin_dir_path( __FILE__ ) . 'view/giftup-cart.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-giftup-cache.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-giftup-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-giftup-options.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-giftup-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-giftup-diagnostics.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/giftup-woocommerce-hooks.php';

// Declare compatability with WC HPOS
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

new giftup_settings( plugin_basename( __FILE__ ), plugin_dir_path( __FILE__ ) );

add_action( 'init', 'giftup_init' );
add_action( 'plugins_loaded', 'giftup_plugins_loaded' );

register_uninstall_hook( __FILE__, 'giftup_uninstall' );
register_activation_hook( __FILE__, 'giftup_activated' );
register_deactivation_hook( __FILE__, 'giftup_deactivated' );

function giftup_plugins_loaded() {
	load_plugin_textdomain( 'gift-up', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function giftup_init() { 
	add_shortcode( 'giftup', 'giftup_shortcode' );

	$current_plugin_version = GIFTUP_VERSION;
	$current_db_version = giftup_options::get_version();

	// Upgrade from v1 standard to v2
	if ( !$current_db_version ) {
		giftup_options::upgrade_from_v1();

		if ( giftup_options::has_api_key()
			&& giftup_options::get_woocommerce_enabled() == null
			&& giftup_diagnostics::woocommerce_installed_version() > 0) {

			$wc_status = giftup_api::get_woocommerce_connection_status();

			if ( $wc_status != null && $wc_status['isConnected'] == true ) {
				giftup_options::set_woocommerce_enabled( true );
			} else {
				giftup_options::set_woocommerce_enabled( false );
			}
		} else {
			giftup_options::set_woocommerce_operating_mode( giftup_options::WOO_MODE_API );
		}

		giftup_options::set_version( $current_plugin_version );
	}
}

function giftup_activated() {
	if ( giftup_options::has_api_key() 
		 && giftup_options::get_woocommerce_enabled()
		 && giftup_options::get_woocommerce_operating_mode() == giftup_options::WOO_MODE_API) {

			giftup_api::notify_connect_woocommerce();
	}
}

function giftup_deactivated() {
	if ( giftup_options::has_api_key()
	 	 && giftup_options::get_woocommerce_operating_mode() == giftup_options::WOO_MODE_API
		 && giftup_options::get_woocommerce_enabled() ) {

			giftup_api::notify_disconnect_woocommerce();
	}
}

function giftup_uninstall() {
	remove_shortcode( 'giftup' );
	giftup_options::disconnect();
}
