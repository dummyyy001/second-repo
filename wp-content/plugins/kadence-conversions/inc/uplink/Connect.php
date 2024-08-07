<?php

namespace KadenceWP\KadenceConversions\Uplink;

use KadenceWP\KadenceConversions\Container;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Register;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Config;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Uplink;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Resources\Collection;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Admin\License_Field;
use function KadenceWP\KadenceConversions\StellarWP\Uplink\get_resource;
use function KadenceWP\KadenceConversions\StellarWP\Uplink\set_license_key;
use function KadenceWP\KadenceConversions\StellarWP\Uplink\get_license_key;
use function KadenceWP\KadenceConversions\StellarWP\Uplink\validate_license;
use function KadenceWP\KadenceConversions\StellarWP\Uplink\get_license_field;
use function is_plugin_active_for_network;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Connect
 * @package KadenceWP\KadenceConversions\Uplink
 */
class Connect {

	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;
	/**
	 * Instance Control
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		// Load licensing.
		add_action( 'plugins_loaded', array( $this, 'load_licensing' ), 2 );
		add_action( 'admin_init', array( $this, 'update_licensing_data' ), 2 );
	}
	/**
	 * Plugin specific text-domain loader.
	 *
	 * @return void
	 */
	public function load_licensing() {
		$container = new Container();
		Config::set_container( $container );
		Config::set_hook_prefix( 'kadence-conversions' );
		Uplink::init();

		$plugin_slug    = 'kadence-conversions';
		$plugin_name    = 'Kadence Conversions';
		$plugin_version = KADENCE_CONVERSIONS_VERSION;
		$plugin_path    = 'kadence-conversions/kadence-conversions.php';
		$plugin_class   = KadenceWP\KadenceConversions\Plugin::class;
		$license_class  = KadenceWP\KadenceConversions\Uplink\Helper::class;

		Register::plugin(
			$plugin_slug,
			$plugin_name,
			$plugin_version,
			$plugin_path,
			$plugin_class,
			$license_class,
		);
		add_filter(
			'stellarwp/uplink/kadence-conversions/api_get_base_url',
			function( $url ) {
				return 'https://licensing.kadencewp.com';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-conversions/messages/valid_key',
			function ( $message, $expiration ) {
				return esc_html__( 'Your license key is valid', 'kadence-pro' );
			},
			10,
			2
		);
		add_filter(
			'stellarwp/uplink/kadence-conversions/admin_js_source',
			function ( $url ) {
				return KADENCE_CONVERSIONS_URL . 'inc/uplink/admin-views/license-admin.js';
			}
		);
		add_filter(
			'stellarwp/uplink/kadence-conversions/admin_css_source',
			function ( $url ) {
				return KADENCE_CONVERSIONS_URL . 'inc/uplink/admin-views/license-admin.css';
			}
		);
		add_filter( 
			'stellarwp/uplink/kadence-conversions/field-template_path',
			function ( $path, $uplink_path ) {
				return KADENCE_CONVERSIONS_PATH . 'inc/uplink/admin-views/field.php';
			},
			10,
			2
		);
		add_filter( 'stellarwp/uplink/kadence-conversions/license_field_html_render', array( $this, 'get_license_field_html' ), 10, 2 );
		add_action( 'network_admin_menu', array( $this, 'create_admin_pages' ), 1 );
		add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
		// Save Network.
		add_action( 'network_admin_edit_kadence_license_update_network_options', array( $this, 'update_network_options' ) );

		add_action( 'kadence_settings_dash_side_panel', array( $this, 'render_settings_field' ) );
	}
	/**
	 * Get license field html.
	 */
	public function get_license_field_html( $field, $args ) {
		$field = sprintf(
			'<div class="%6$s" id="%2$s" data-slug="%2$s" data-plugin="%9$s" data-plugin-slug="%10$s" data-action="%11$s">
					<fieldset class="stellarwp-uplink__settings-group">
						<div class="stellarwp-uplink__settings-group-inline">
						%12$s
						%13$s
						</div>
						<input type="%1$s" name="%3$s" value="%4$s" placeholder="%5$s" class="regular-text stellarwp-uplink__settings-field" />
						%7$s
					</fieldset>
					%8$s
				</div>',
			! empty( $args['value'] ) ? 'hidden' : 'text',
			esc_attr( $args['path'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['value'] ),
			esc_attr( __( 'License Key', 'kadence-blocks' ) ),
			esc_attr( $args['html_classes'] ?: '' ),
			$args['html'],
			'<input type="hidden" value="' . wp_create_nonce( 'stellarwp_uplink_group_' ) . '" class="wp-nonce" />',
			esc_attr( $args['plugin'] ),
			esc_attr( $args['plugin_slug'] ),
			esc_attr( Config::get_hook_prefix_underscored() ),
			! empty( $args['value'] ) ? '<input type="text" name="obfuscated-key" disabled value="' . $this->obfuscate_key( $args['value'] ) . '" class="regular-text stellarwp-uplink__settings-field-obfuscated" />' : '',
			! empty( $args['value'] ) ? '<button type="submit" class="button button-secondary stellarwp-uplink-license-key-field-clear">' . esc_html__( 'Clear', 'kadence-pro' ) . '</button>' : ''
		);

		return $field;
	}
	/**
	 * Check if network authorize is enabled.
	 */
	public function is_network_authorize_enabled() {
		$network_enabled = ! apply_filters( 'kadence_activation_individual_multisites', true );
		if ( ! $network_enabled && defined( 'KADENCE_ACTIVATION_NETWORK_ENABLED' ) && KADENCE_ACTIVATION_NETWORK_ENABLED ) {
			$network_enabled = true;
		}
		return $network_enabled;
	}
	/**
	 * This function here is hooked up to a special action and necessary to process
	 * the saving of the options. This is the big difference with a normal options
	 * page.
	 */
	public function update_network_options() {
		$options_id = $_REQUEST['option_page'];

		// Make sure we are posting from our options page.
		check_admin_referer( $options_id . '-options' );
		if ( isset( $_POST[ 'stellarwp_uplink_license_key_kadence-conversions' ] ) ) {
			$value = sanitize_text_field( trim( $_POST[ 'stellarwp_uplink_license_key_kadence-conversions' ] ) );
			set_license_key( 'kadence-conversions', $value );

			// At last we redirect back to our options page.
			wp_redirect( network_admin_url( 'settings.php?page=kadence-conversions-license' ) );
			exit;
		}
	}
	/**
	 * Register settings
	 */
	public function create_admin_pages() {
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( 'kadence-conversions/kadence-conversions.php' ) && $this->is_network_authorize_enabled() ) {
			add_action( 'network_admin_menu', function() {
				add_submenu_page( 'settings.php',  __( 'Kadence Conversions - License', 'kadence-conversions' ), __( 'Kadence Conversions License', 'kadence-conversions' ), 'manage_options', 'kadence-conversions-license', array( $this, 'render_network_settings_page' ), 999 );
			}, 21 );
		}
	}
	/**
	 * Obfuscate license key.
	 */
	public function obfuscate_key( $key ) {
		$start = 3;
		$length = mb_strlen( $key ) - $start - 3;
		$mask_string = preg_replace( '/\S/', 'X', $key );
		$mask_string = mb_substr( $mask_string, $start, $length );
		$input_string = substr_replace( $key, $mask_string, $start, $length );
		return $input_string;
	}
	/**
	 * Register settings
	 */
	public function render_network_settings_page() {
		$slug       = 'kadence-conversions';
		$field      = get_license_field();
		$key        = get_license_key( $slug );
		$group      = $field->get_group_name( sanitize_title( $slug ) );
		wp_enqueue_script( sprintf( 'stellarwp-uplink-license-admin-%s', $slug ) );
		wp_enqueue_style( sprintf( 'stellarwp-uplink-license-admin-%s', $slug ) );
		echo '<h3>Kadence Theme Pro</h3>';
		echo '<form action="edit.php?action=kadence_license_update_network_options" method="post" id="kadence-license-kadence-conversions">';
		settings_fields( $group );
		$html = sprintf( '<p class="tooltip description">%s</p>', __( 'A valid license key is required for support and updates', 'kadence-conversions' ) );
		$html .= '<div class="license-test-results"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading-license" alt="Loading" style="display: none"/>';
		$html .= '<div class="key-validity"></div></div>';
		echo '<div class="stellarwp-uplink__license-field">';
		echo '<label for="stellarwp_uplink_license_key_kadence-conversions">' . esc_attr__( 'License Key', 'kadence-conversions' ) . '</label>';
		$args = array(
			'type' => 'text',
			'path' => 'kadence-conversions/kadence-conversions.php',
			'id' => 'stellarwp_uplink_license_key_kadence-conversions',
			'value' => $key,
			'placeholder' => esc_attr__( 'License Key', 'kadence-conversions' ),
			'html_classes' => 'stellarwp-uplink-license-key-field',
			'html' => $html,
			'plugin' => 'kadence-conversions/kadence-conversions.php',
			'plugin_slug' => 'kadence-conversions',
		);
		echo $this->get_license_field_html( '', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		if ( empty( $key ) ) {
			submit_button( esc_html__( 'Save Changes', 'kadence-conversions' ) );
		}
		echo '</form>';
	}
	/**
	 * Register settings
	 */
	public function render_settings_field( $slug ) {
		//error_log( 'render_settings_field', $slug );
		if ( empty( $slug ) || 'kadence_conversions' !== $slug ) {
			return;
		}
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( 'kadence-conversions/kadence-conversions.php' ) && $this->is_network_authorize_enabled() ) {
			?>
			<div class="license-section sidebar-section components-panel">
				<div class="components-panel__body is-opened">
					<?php
					echo esc_html__( 'Network License Controlled', 'kadence-conversions' );
					?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="license-section sidebar-section components-panel">
				<div class="components-panel__body is-opened">
					<?php
					get_license_field()->render_single( 'kadence-conversions' );
					?>
				</div>
			</div>
			<?php
		}
	}
	/**
	 * Update licensing data.
	 */
	public function update_licensing_data() {
		$updated     = get_option( 'kadence-conversions-license-updated', false );
		if ( ! $updated ) {
			$key = get_license_key( 'kadence-conversions' );
			if ( empty( $key ) ) {
				$license_data = $this->get_deprecated_pro_license_data();
				if ( $license_data && ! empty( $license_data['api_key'] ) ) {
					set_license_key( 'kadence-conversions', $license_data['api_key'] );
					update_option( 'kadence-conversions-license-updated', true );
				} else if ( $license_data && ! empty( $license_data['ithemes_key'] ) && ! empty( $license_data['username'] ) ) {
					$license_key = $this->get_new_key_for_ithemes_user_data( $license_data['username'], $license_data['ithemes_key'] );
					if ( ! empty( $license_key ) ) {
						set_license_key( 'kadence-conversions', $license_key );
						update_option( 'kadence-conversions-license-updated', true );
					} else {
						update_option( 'kadence-conversions-license-updated', true );
					}
				} else {
					update_option( 'kadence-conversions-license-updated', true );
				}
			}
		}
	}
	/**
	 * Get the old license information.
	 *
	 * @return array
	 */
	public function get_new_key_for_ithemes_user_data( $username, $key ) {
		if ( is_callable( 'network_home_url' ) ) {
			$site_url = network_home_url( '', 'http' );
		} else {
			$site_url = get_bloginfo( 'url' );
		}
		$site_url = preg_replace( '/^https/', 'http', $site_url );
		$site_url = preg_replace( '|/$|', '', $site_url );
		$args = array(
			'wc-api'       => 'kadence_itheme_key_update',
			'username'     => $username,
			'private_hash' => $key,
			'site_url'     => $site_url,
		);
		$url  = add_query_arg( $args, 'https://www.kadencewp.com/' );
		$response = wp_safe_remote_get( $url );
		// Early exit if there was an error.
		if ( is_wp_error( $response ) ) {
			return false;
		}
		// Get the body from our response.
		$new_key = wp_remote_retrieve_body( $response );
		// Early exit if there was an error.
		if ( is_wp_error( $new_key ) ) {
			return false;
		}
		$new_key = json_decode( trim( $new_key ), true );
		if ( is_string( $new_key ) && substr( $new_key, 0, 3 ) === "ktm" ) {
			return $new_key;
		}
		return false;
	}
	/**
	 * Get the old license information.
	 *
	 * @return array
	 */
	public function get_deprecated_pro_license_data() {
		$data = false;
		if ( is_multisite() && ! apply_filters( 'kadence_activation_individual_multisites', true ) ) {
			$data = get_site_option( 'kt_api_manager_kadence_conversions_data' );
		} else {
			$data = get_option( 'kt_api_manager_kadence_conversions_data' );
		}
		return $data;
	}
	/**
	 * Displays an inactive notice when the software is inactive.
	 */
	public function inactive_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['page'] ) && ( 'kadence-conversion-settings' == $_GET['page'] ) ) {
			// For Now, clear when on the settings page.
			set_transient( 'kadence_conversions_license_status_check', false );
			return;
		}
		$valid_license   = false;
		$network_enabled = $this->is_network_authorize_enabled();
		// Add below once we've given time for everyones cache to update.
		// $plugin          = get_resource( 'kadence-conversions' );
		// if ( $plugin ) {
		// 	$valid_license = $plugin->has_valid_license();
		// }
		$key = get_license_key( 'kadence-conversions' );
		if ( ! empty( $key ) ) {
			// Check with transient first, if not then check with server.
			$status = get_transient( 'kadence_conversions_license_status_check' );
			if ( false === $status || ( strpos( $status, $key ) === false ) ) {
				$license_data = validate_license( 'kadence-conversions', $key );
				if ( isset( $license_data ) && is_object( $license_data ) && method_exists( $license_data, 'is_valid' ) && $license_data->is_valid() ) {
					$status = 'valid';
				} else {
					$status = 'invalid';
				}
				$status = $key . '_' . $status;
				set_transient( 'kadence_conversions_license_status_check', $status, WEEK_IN_SECONDS );
			}
			if ( strpos( $status, $key ) !== false ) {
				$valid_check = str_replace( $key . '_', '', $status );
				if ( 'valid' === $valid_check ) {
					$valid_license = true;
				}
			}
		}
		if ( ! $valid_license ) {
			if ( is_plugin_active_for_network( 'kadence-conversions/kadence-conversions.php' ) && $network_enabled ) {
				if ( current_user_can( 'manage_network_options' ) ) {
					echo '<div class="error">';
					echo '<p>' . esc_html__( 'Kadence Conversions has not been activated.', 'kadence-conversions' ) . ' <a href="' . esc_url( network_admin_url( 'settings.php?page=kadence-conversions-license' ) ) . '">' . __( 'Click here to activate.', 'kadence-conversions' ) . '</a></p>';
					echo '</div>';
				}
			} else {
				echo '<div class="error">';
				echo '<p>' . __( 'Kadence Conversions has not been activated.', 'kadence-conversions' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=kadence-conversion-settings' ) ) . '">' . __( 'Click here to activate.', 'kadence-conversions' ) . '</a></p>';
				echo '</div>';
			}
		}
	}
}
Connect::get_instance();
