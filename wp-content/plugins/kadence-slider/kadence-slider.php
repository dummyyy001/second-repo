<?php
/**
 * Plugin Name: Kadence Slider
 * Description: Responsive image slider with css animations for layers.
 * Version: 2.3.6
 * Author: Kadence WP
 * Author URI: http://kadencewp.com/
 * License: GPLv2 or later
 *
 * @package Kadence Slider Pro
 */

// Define constants.
add_action( 'plugins_loaded', 'kadence_slider_init' );
function kadence_slider_init() {
	define( 'KADENCE_SLIDER_PATH', realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR );
	define( 'KADENCE_SLIDER_URL', plugin_dir_url(__FILE__) );

	define( 'KS_VERSION', '2.3.6' );
	define( 'KS_DEBUG', false );

	// Legacy Slider
	require_once( KADENCE_SLIDER_PATH . 'kadence-slider-legacy.php');

	// Frontoutput class
	require_once( KADENCE_SLIDER_PATH . 'kadence-slider-pro-frontend.php');
	KadenceSliderPro_Output::addShortcode();

	// Admin functions
	require_once( KADENCE_SLIDER_PATH . 'admin/ks-manage.php');
	require_once( KADENCE_SLIDER_PATH . 'admin/database.php');
	require_once( KADENCE_SLIDER_PATH . 'admin/typography/typography.php');

	if(is_admin()) {
		if(KS_DEBUG || KS_VERSION != get_option('ksp_version')) {
		  KadenceSliderProDatabase::setDatabase();
		}
		if(KS_VERSION != get_option('ksp_version')) {
		  KadenceSliderProDatabase::ksp_setversion();
		}

		KadenceSliderAdmin::ksp_init_admin();
		KadenceSliderAdmin::ksp_hook_admin_scripts();

		require_once (KADENCE_SLIDER_PATH . 'admin/ajax_functions.php');
		require_once (KADENCE_SLIDER_PATH . 'admin/slider_preview.php');
	}
}
// Frontend Scripts
function kadence_slider_scripts() {
	wp_enqueue_style( 'kadence_slider_css',  KADENCE_SLIDER_URL . 'css/ksp.css', array(), KS_VERSION );
	wp_register_script( 'kadence_slider_js', KADENCE_SLIDER_URL . 'js/min/ksp-min.js', array( 'jquery' ), KS_VERSION, true);
}
add_action( 'wp_enqueue_scripts', 'kadence_slider_scripts', 100 );
add_action( 'wp_enqueue_scripts', 'ksp_remove_scripts', 160 );
function ksp_remove_scripts(){
  	global $kadence_slider;
  	if ( isset( $kadence_slider['ksp_load_fonts'] ) && $kadence_slider['ksp_load_fonts'] == 0 ) {
   		wp_dequeue_style('redux-google-fonts-kadence_slider');
  	}
}


function ksp_get_image_id_by_link( $attachment_url ){
	global $wpdb;
	$attachment_id = false;

	// If there is no url, return.
	if ( '' == $attachment_url ) {
		return;
	}

	// Define upload path & dir.
	$upload_info = wp_upload_dir();
	$upload_dir = $upload_info['basedir'];
	$upload_url = $upload_info['baseurl'];

	$http_prefix = "http://";
	$https_prefix = "https://";
	$relative_prefix = "//"; // The protocol-relative URL

	/* if the $url scheme differs from $upload_url scheme, make them match 
	if the schemes differe, images don't show up. */
	if( ! strncmp( $attachment_url, $https_prefix, strlen( $https_prefix ) ) ) { //if url begins with https:// make $upload_url begin with https:// as well
		$upload_url = str_replace( $http_prefix, $https_prefix, $upload_url) ;
	} else if ( ! strncmp( $attachment_url, $http_prefix, strlen( $http_prefix ) ) ) { //if url begins with http:// make $upload_url begin with http:// as well
		$upload_url = str_replace( $https_prefix, $http_prefix, $upload_url );      
	} else if ( ! strncmp( $attachment_url, $relative_prefix, strlen( $relative_prefix ) ) ){ //if url begins with // make $upload_url begin with // as well
		$upload_url = str_replace( array( 0 => "$http_prefix", 1 => "$https_prefix"), $relative_prefix, $upload_url );
	}

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_url ) ) {
			
			$attachment_new_url = str_replace( $upload_url . '/', '', $attachment_url );
			
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_new_url ) );

		if( ! $attachment_id ) {
			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
	 
			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_url . '/', '', $attachment_url );
	 
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
		}

	}
 
	return $attachment_id;
}

/**
 * Handle plugin updates.
 */
function kadence_slider_pro_updating() {
	require_once KADENCE_SLIDER_PATH . 'kadence-update-checker/kadence-update-checker.php';
	$kadence_slider_update_checker = Kadence_Update_Checker::buildUpdateChecker(
		'https://kernl.us/api/v1/updates/5679e8dd6f276b6452e41eb4/',
		__FILE__,
		'kadence-slider'
	);
}
add_action( 'after_setup_theme', 'kadence_slider_pro_updating', 1 );
