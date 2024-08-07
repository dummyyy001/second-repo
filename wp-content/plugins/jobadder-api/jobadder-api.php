<?php
/**
 * Plugin Name: Jobadder API
 * Version: 1.0.8
 * Author: BrandH2O
 * Text Domain: bh2ojaa
 */
 
defined( 'ABSPATH' ) || die( 'You are not allowed to access.' ); // Terminate if accessed directly

if ( !defined( 'BH2OJAA_PLUGIN_FILE' ) ) {
	define( 'BH2OJAA_PLUGIN_FILE', __FILE__ );
}

if ( !defined( 'BH2OJAA_PLUGIN_VERSION' ) ) {
	define( 'BH2OJAA_PLUGIN_VERSION', '1.0.7' );
}

function bh2ojaa_option_exists( $option_name, $site_wide = false ) {
	global $wpdb; 
    return $wpdb->query( $wpdb->prepare( "SELECT * FROM ". ($site_wide ? $wpdb->base_prefix : $wpdb->prefix). "options WHERE option_name ='%s' LIMIT 1", $option_name ) );
}

register_activation_hook( __FILE__, function() {
	if ( !bh2ojaa_option_exists( 'bh2ojaa_options' ) ) {
    	add_option( 'bh2ojaa_options', array() );
    }
} );

require_once( plugin_dir_path( __FILE__ ) . 'inc/cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'inc/class-jobadder-api.php' );
require_once( plugin_dir_path( __FILE__ ) . 'inc/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'inc/admin/class-admin-jobadder-api.php' );