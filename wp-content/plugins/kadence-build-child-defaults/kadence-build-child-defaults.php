<?php
/**
 * Plugin Name: Kadence Child Theme Builder
 * Description: Easily create a child theme with custom theme defaults and a custom starter template.
 * Version: 1.0.7
 * Author: Kadence WP
 * Author URI: https://kadencewp.com/
 * License: GPLv2 or later
 * Text Domain: kadence-child-theme-builder
 *
 * @package Kadence Child Theme Builder
 */

// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! version_compare( PHP_VERSION, '7.0', '>=' ) ) {
	add_action( 'admin_notices', 'kadence_build_child_defaults_old_php_admin_error_notice' );
} else {
	require_once 'class-kadence-build-child-defaults.php';
}
/**
 * Display an admin error notice when PHP is older the version 5.3.2.
 * Hook it to the 'admin_notices' action.
 */
function kadence_build_child_defaults_old_php_admin_error_notice() {
	$message = __( 'Kadence Child Theme Builder requires at least PHP 7.0 to run properly. Please contact your hosting company and ask them to update the PHP version of your site to at least PHP 7.0. We strongly encourage you to update to 7.3+', 'kadence-child-theme-builder' );

	printf( '<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post( $message ) );
}

add_action( 'cmb2_admin_init', 'kadence_build_child_register_options' );
/**
 * Hook in and register a metabox to handle a theme options page and adds a menu item.
 */
function kadence_build_child_register_options() {

	$cmb_options = new_cmb2_box( array(
		'id'           => 'kadence_build_child',
		'title'        => esc_html__( 'Child Builder', 'kadence-child-theme-builder' ),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'kadence_build_child_theme_config',
		//'parent_slug'  => 'themes.php',
		'icon_url'     => 'dashicons-welcome-widgets-menus',
		'tab_group'    => 'kadence_build_child_main_options',
		'tab_title'    => 'Child Theme Config',
		'display_cb'   => 'kadence_build_child_page_output', // Override the options-page form output (CMB2_Hookup::options_page_output()).
		'description'  => 'Build a custom child theme for the Kadence Theme', // Will be displayed via our display_cb.
	) );
	$cmb_options_demo = new_cmb2_box( array(
		'id'           => 'kadence_build_child_demo',
		'title'        => esc_html__( 'Demo Config', 'kadence-child-theme-builder' ),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'kadence_build_child_demo_config',
		'parent_slug'  => 'kadence_build_child_theme_config',
		'tab_group'    => 'kadence_build_child_main_options',
		'tab_title'    => 'Demo Import Config',
		'display_cb'   => 'kadence_build_child_page_output', // Override the options-page form output (CMB2_Hookup::options_page_output()).
	) );
	$cmb_options_cloud = new_cmb2_box( array(
		'id'           => 'kadence_build_child_cloud',
		'title'        => esc_html__( 'Cloud Config', 'kadence-child-theme-builder' ),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'kadence_build_child_cloud_config',
		'parent_slug'  => 'kadence_build_child_theme_config',
		'tab_group'    => 'kadence_build_child_main_options',
		'tab_title'    => 'Cloud Library Config',
		'display_cb'   => 'kadence_build_child_page_output', // Override the options-page form output (CMB2_Hookup::options_page_output()).
	) );
	$cmb_options_generate = new_cmb2_box( array(
		'id'           => 'kadence_build_child_generate',
		'title'        => esc_html__( 'Generate', 'kadence-child-theme-builder' ),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'kadence_build_child_generate_config',
		'parent_slug'  => 'kadence_build_child_theme_config',
		'tab_group'    => 'kadence_build_child_main_options',
		'tab_title'    => 'Generate Child Theme',
		'display_cb'   => 'kadence_build_child_page_output', // Override the options-page form output (CMB2_Hookup::options_page_output()).
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Child Theme Name', 'kadence-child-theme-builder' ),
		'id'         => 'kct_name',
		'type'       => 'text',
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Child Theme Slug', 'kadence-child-theme-builder' ),
		'id'         => 'kct_slug',
		'desc'       => esc_html__( 'This is the folder name for the child theme, should be unique and without spaces.', 'kadence-child-theme-builder' ),
		'type'       => 'text',
		'sanitization_cb' => 'kadence_build_child_sanitize_slug', // custom sanitization callback parameter
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Author', 'kadence-child-theme-builder' ),
		'id'         => 'kct_author',
		'type'       => 'text',
	) );
	$cmb_options->add_field( array(
		'name' => esc_html__( 'Child Theme Description', 'kadence-child-theme-builder' ),
		'id' => 'kct_description',
		'type' => 'textarea_small'
	) );
	$cmb_options->add_field( array(
		'name'    => esc_html__( 'Child Theme Screenshot', 'kadence-child-theme-builder' ),
		'desc'    => esc_html__( 'Upload an image 1200×900px for best results', 'kadence-child-theme-builder' ),
		'id'      => 'kct_screenshot',
		'type'    => 'file',
		'options' => array(
			'url' => false, // Hide the text input for the url
		),
		'text'    => array(
			'add_upload_file_text' => esc_html__( 'Add or Upload Image', 'kadence-child-theme-builder' ), // Change upload button text. Default: "Add or Upload File"
		),
		// query_args are passed to wp.media's library query.
		'query_args' => array(
			//'type' => 'application/pdf', // Make library only display PDFs.
			// Or only allow gif, jpg, or png images
			'type' => array(
				'image/gif',
				'image/jpeg',
				'image/png',
			),
		),
		'preview_size' => 'medium', // Image size to use when previewing in the admin.
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Optional - Theme URL', 'kadence-child-theme-builder' ),
		'id'         => 'kct_theme_url',
		'type'       => 'text_url',
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Optional - Author URL', 'kadence-child-theme-builder' ),
		'id'         => 'kct_author_url',
		'type'       => 'text_url',
	) );
	$cmb_options->add_field( array(
		'name'       => esc_html__( 'Optional - Version', 'kadence-child-theme-builder' ),
		'id'         => 'kct_theme_version',
		'type'       => 'text_small',
		'sanitization_cb' => 'kadence_build_child_sanitize_version', // custom sanitization callback parameter
	) );
	// Demo Content Importer.
	$cmb_options_demo->add_field( array(
		'name'       => esc_html__( 'Template Name', 'kadence-child-theme-builder' ),
		'id'         => 'kct_template_name',
		'type'       => 'text',
	) );
	$cmb_options_demo->add_field( array(
		'name'       => esc_html__( 'Template Preview URL', 'kadence-child-theme-builder' ),
		'id'         => 'kct_template_url',
		'type'       => 'text_url',
	) );
	$cmb_options_demo->add_field( array(
		'name'    => esc_html__( 'Template Preview Image', 'kadence-child-theme-builder' ),
		'desc'    => esc_html__( 'Upload an image at least 800px wide and as tall as your page.', 'kadence-child-theme-builder' ),
		'id'      => 'kct_template_image',
		'type'    => 'file',
		'options' => array(
			'url' => false, // Hide the text input for the url
		),
		'text'    => array(
			'add_upload_file_text' => 'Add or Upload Image' // Change upload button text. Default: "Add or Upload File"
		),
		// query_args are passed to wp.media's library query.
		'query_args' => array(
			//'type' => 'application/pdf', // Make library only display PDFs.
			// Or only allow gif, jpg, or png images
			'type' => array(
				'image/gif',
				'image/jpeg',
				'image/png',
			),
		),
		'preview_size' => 'medium', // Image size to use when previewing in the admin.
	) );
	$cmb_options_demo->add_field( array(
		'name'       => esc_html__( 'Template Brand Name', 'kadence-child-theme-builder' ),
		'id'         => 'kct_template_brand_name',
		'type'       => 'text',
	) );
	$cmb_options_demo->add_field( array(
		'name'    => esc_html__( 'Template Brand Logo', 'kadence-child-theme-builder' ),
		'desc'    => esc_html__( 'Upload a square brand logo image.', 'kadence-child-theme-builder' ),
		'id'      => 'kct_template_brand_image',
		'type'    => 'file',
		'options' => array(
			'url' => false, // Hide the text input for the url
		),
		'text'    => array(
			'add_upload_file_text' => 'Add or Upload Image' // Change upload button text. Default: "Add or Upload File"
		),
		// query_args are passed to wp.media's library query.
		'query_args' => array(
			//'type' => 'application/pdf', // Make library only display PDFs.
			// Or only allow gif, jpg, or png images
			'type' => array(
				'image/gif',
				'image/jpeg',
				'image/png',
			),
		),
		'preview_size' => 'thumbnail', // Image size to use when previewing in the admin.
	) );
	// Cloud.
	$cmb_options_cloud->add_field( array(
		'name'       => esc_html__( 'Cloud Name', 'kadence-child-theme-builder' ),
		'id'         => 'kct_cloud_name',
		'type'       => 'text',
	) );
	$cmb_options_cloud->add_field( array(
		'name'       => esc_html__( 'Cloud Access URL', 'kadence-child-theme-builder' ),
		'id'         => 'kct_cloud_url',
		'type'       => 'text_url',
	) );
	$cmb_options_cloud->add_field( array(
		'name'       => esc_html__( 'Cloud Access Key', 'kadence-child-theme-builder' ),
		'id'         => 'kct_cloud_key',
		'type'       => 'text',
	) );
}

function kadence_build_child_page_output( $hookup ) {
	// Output custom markup for the options-page.
	$tabs = kadence_build_child_options_page_tabs( $hookup );
	?>
	<div class="wrap cmb2-options-page option-<?php echo $hookup->option_key; ?>">
		<?php if ( $hookup->cmb->prop( 'title' ) ) : ?>
			<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'title' ) ); ?></h2>
		<?php endif; ?>
		<?php if ( $hookup->cmb->prop( 'description' ) ) : ?>
			<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'description' ) ); ?></h2>
		<?php endif; ?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
				<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
			<?php endforeach; ?>
		</h2>
		<?php 
		if ( 'kadence_build_child_generate' === $hookup->cmb->cmb_id ) {
			wp_enqueue_script( 'kadence_child_generator' );
			echo '<a class="kadence-generate-child-button button button-primary">' . esc_html__( 'Generate Child Theme', 'kadence-build-child-defaults' ) . '</a>';
		} else {	
			?>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $hookup->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $hookup->option_key ); ?>">
				<?php $hookup->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $hookup->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
			<?php
		} ?>
	</div>
	<?php
}
/**
 * Gets navigation tabs array for CMB2 options pages which share the given
 * display_cb param.
 *
 * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
 *
 * @return array Array of tab information.
 */
function kadence_build_child_options_page_tabs( $cmb_options ) {
	$tab_group = $cmb_options->cmb->prop( 'tab_group' );
	$tabs      = array();

	foreach ( CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
		if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
			$tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
				? $cmb->prop( 'tab_title' )
				: $cmb->prop( 'title' );
		}
	}

	return $tabs;
}
/**
 * Handles sanitization for the wiki_custom_escaping_and_sanitization field.
 * Ensures a field's value is greater than 100 or nothing.
 *
 * @param  mixed      $value      The unsanitized value from the form.
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 *
 * @return mixed                  Sanitized value to be stored.
 */
function kadence_build_child_sanitize_slug( $value, $field_args, $field ) {
	return sanitize_title_with_dashes( $value );
}
/**
 * Handles sanitization for the wiki_custom_escaping_and_sanitization field.
 * Ensures a field's value is greater than 100 or nothing.
 *
 * @param  mixed      $value      The unsanitized value from the form.
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 *
 * @return mixed                  Sanitized value to be stored.
 */
function kadence_build_child_sanitize_version( $value, $field_args, $field ) {
	return str_replace( ' ', '_', sanitize_text_field( $value ) );
}
/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function kadence_build_child_get_option( $section = '', $key = '', $default = false ) {
	if ( empty( $section ) ) {
		return '';
	}
	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( $section, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( $section, $default );

	$val = $default;

	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}