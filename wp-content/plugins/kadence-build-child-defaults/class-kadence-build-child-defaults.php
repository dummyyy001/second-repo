<?php
/**
 * Importer class.
 *
 * @package Kadence Build Child Defaults
 */

/**
 * Block direct access to the main plugin file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Main plugin class with initialization tasks.
 */
class Kadence_Build_Child_Defaults {
	/**
	 * An array of core options that shouldn't be imported.
	 * @access private
	 * @var array $core_options
	 */
	static private $core_options = array(
		'blogname',
		'blogdescription',
		'show_on_front',
		'page_on_front',
		'page_for_posts',
	);
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
	 * Construct function
	 */
	public function __construct() {
		define( 'KADENCE_BUILD_CHILD_PATH', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR );
		define( 'KADENCE_BUILD_CHILD_URL', plugin_dir_url( __FILE__ ) );
		add_action( 'after_setup_theme', array( $this, 'updater' ), 5 );
		if ( file_exists( KADENCE_BUILD_CHILD_PATH . 'includes/cmb2/init.php' ) ) {
			require_once KADENCE_BUILD_CHILD_PATH . 'includes/cmb2/init.php';
		}
		require_once KADENCE_BUILD_CHILD_PATH . 'class-kadence-build-child-plugin-check.php';
		if ( is_admin() ) {
			add_action( 'wp_ajax_kct_export_child',  array( $this, 'export_callback' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 10 );
		}
		// add_action( 'init', array( $this, 'init_config' ) );
		// add_action( 'admin_init', array( $this, 'load_settings' ) );
	}
	/**
	 * Update the plugin.
	 */
	public function updater() {
		require_once KADENCE_BUILD_CHILD_PATH . 'kadence-update-checker/kadence-update-checker.php';
		require_once KADENCE_BUILD_CHILD_PATH . 'kadence-activation/updater.php';
	}
	/**
	 * create a slug.
	 */
	public function slugify( $text, string $divider = '-' ) {
		// replace non letter or digits by divider
		$text = preg_replace('~[^\pL\d]+~u', $divider, $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, $divider);

		// remove duplicate divider
		$text = preg_replace('~-+~', $divider, $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
	/**
	 * Output Functions.php
	 */
	public function get_php() {
		// $plugins = array (
		// 	'woocommerce' => array(
		// 		'title' => 'Woocommerce',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'woocommerce/woocommerce.php' ),
		// 		'src'   => 'repo',
		// 	),
		// 	'elementor' => array(
		// 		'title' => 'Elementor',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'elementor/elementor.php' ),
		// 		'src'   => 'repo',
		// 	),
		// 	'kadence-blocks' => array(
		// 		'title' => 'Kadence Blocks',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'kadence-blocks/kadence-blocks.php' ),
		// 		'src'   => 'repo',
		// 	),
		// 	'kadence-blocks-pro' => array(
		// 		'title' => 'Kadence Block Pro',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'kadence-blocks-pro/kadence-blocks-pro.php' ),
		// 		'src'   => 'bundle',
		// 	),
		// 	'kadence-pro' => array(
		// 		'title' => 'Kadence Pro',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'kadence-pro/kadence-pro.php' ),
		// 		'src'   => 'bundle',
		// 	),
		// 	'fluentform' => array(
		// 		'title' => 'Fluent Forms',
		// 		'src'   => 'repo',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'fluentform/fluentform.php' ),
		// 	),
		// 	'wpzoom-recipe-card' => array(
		// 		'title' => 'Recipe Card Blocks by WPZOOM',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'recipe-card-blocks-by-wpzoom/wpzoom-recipe-card.php' ),
		// 		'src'   => 'repo',
		// 	),
		// 	'learndash' => array(
		// 		'title' => 'LearnDash',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'sfwd-lms/sfwd_lms.php' ),
		// 		'src'   => 'thirdparty',
		// 	),
		// 	'lifterlms' => array(
		// 		'title' => 'LifterLMS',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'lifterlms/lifterlms.php' ),
		// 		'src'   => 'repo',
		// 	),
		// 	'tutor' => array(
		// 		'title' => 'Tutor LMS',
		// 		'state' => Kadence_Builder_Child_Plugin_Check::active_check( 'tutor/tutor.php' ),
		// 		'src'   => 'repo',
		// 	),
		// );
		$plugin_exclude = array(
			'kadence-starter-templates/kadence-starter-templates.php',
			'kadence-build-child-defaults/kadence-build-child-defaults.php',
		);
		$plugins = get_option( 'active_plugins' );
		$plugin_array = array();
		foreach ( $plugins as $key => $base ) {
			if ( ! in_array( $base, $plugin_exclude ) ) {
				$plugin_array[] = $base;
			}
		}
		$child_name = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_name', 'Pro Design' );
		$demo_name = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_name', '' );
		$demo_url = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_url', '' );
		$demo_image = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_image', '' );
		$child_slug = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_slug', '' );
		if ( ! empty( $child_slug ) ) {
			$slug = str_replace( '-', '_', $this->slugify( $child_slug ) );
		} else {
			$slug = str_replace( '-', '_', $this->slugify( $child_name ) );
		}
		$palette = get_option( 'kadence_global_palette' );
		$mods = get_theme_mods();
		$menus_output = '';
		if ( isset( $mods['nav_menu_locations'] ) && is_array( $mods['nav_menu_locations'] ) ) {
			foreach ( $mods['nav_menu_locations'] as $key => $value ) {
				$menu = get_term_by( 'id', $value, 'nav_menu' );
				$menus_output .= '\'' . $key . '\' => array(
				\'menu\'  => \'' . $key . '\',
				\'title\' => \'' . $menu->name . '\',
			),
';
			}
		}
		$child_theme_version = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_theme_version', '1.0' );
		$custom_css = '';
		if ( is_child_theme() ) {
			$child_theme_styles = $this->get_file_contents( get_stylesheet_directory() . '/style.css' );
			$child_theme_styles = str_replace( "/*","_COMSTART", $child_theme_styles);
			$child_theme_styles = str_replace( "*/","COMEND_", $child_theme_styles );
			$child_theme_styles = preg_replace( "/_COMSTART.*?COMEND_/s", "", $child_theme_styles );
			if ( ! empty( $child_theme_styles ) ) {
				$custom_css .= $child_theme_styles;
			}
		}
		$custom_css .= wp_get_custom_css();
		$output = '<?php
/**
 * Setup Child Theme Styles
 */
function ' . $slug . '_enqueue_styles() {
	wp_enqueue_style( \'' . $slug . '-style\', get_stylesheet_directory_uri() . \'/style.css\', false, \'' . $child_theme_version . '\' );
}
';
if ( ! empty( $custom_css ) ) {
	$output .= 'add_action( \'wp_enqueue_scripts\', \'' . $slug . '_enqueue_styles\', 20 );

';
} else {
	$output .= '// add_action( \'wp_enqueue_scripts\', \'' . $slug . '_enqueue_styles\', 20 );

';
}
		if ( ! empty( $palette ) ) {
			$output .= '
/**
 * Setup Child Theme Palettes
 *
 * @param string $palettes registered palette json.
 * @return string
 */
function ' . $slug . '_change_palette_defaults( $palettes ) {
	$palettes = \'' . $palette . '\';
	return $palettes;
}
add_filter( \'kadence_global_palette_defaults\', \'' . $slug . '_change_palette_defaults\', 20 );';
		}
		if ( ! empty( $mods ) ) {
			// Don't need empty item.
			if ( isset( $mods[0] ) ) {
				unset( $mods[0] );
			}
			// Don't need nav menu locations.
			if ( isset( $mods['nav_menu_locations'] ) ) {
				unset( $mods['nav_menu_locations'] );
			}
			// Don't need widget locations.
			if ( isset( $mods['sidebars_widgets'] ) ) {
				unset( $mods['sidebars_widgets'] );
			}
			// Don't need custom css.
			if ( isset( $mods['custom_css_post_id'] ) ) {
				unset( $mods['custom_css_post_id'] );
			}
			// Prevent a breakage with options html.
			if ( isset( $mods['footer_html_content'] ) && ! empty( $mods['footer_html_content'] ) ) {
				$mods['footer_html_content'] = str_replace( '<p>', '', $mods['footer_html_content'] );
				$mods['footer_html_content'] = str_replace( '</p>', '', $mods['footer_html_content'] );
			}
			if ( isset( $mods['header_html_content'] ) && ! empty( $mods['header_html_content'] ) ) {
				$mods['header_html_content'] = str_replace( '<p>', '', $mods['header_html_content'] );
				$mods['header_html_content'] = str_replace( '</p>', '', $mods['header_html_content'] );
			}
			if ( isset( $mods['mobile_html_content'] ) && ! empty( $mods['mobile_html_content'] ) ) {
				$mods['mobile_html_content'] = str_replace( '<p>', '', $mods['mobile_html_content'] );
				$mods['mobile_html_content'] = str_replace( '</p>', '', $mods['mobile_html_content'] );
			}
			$output .= '

/**
 * Setup Child Theme Defaults
 *
 * @param array $defaults registered option defaults with kadence theme.
 * @return array
 */
function ' . $slug . '_change_option_defaults( $defaults ) {
	$new_defaults = \'' . json_encode( $mods, JSON_HEX_APOS ) . '\';
	$new_defaults = json_decode( $new_defaults, true );
	return wp_parse_args( $new_defaults, $defaults );
}
add_filter( \'kadence_theme_options_defaults\', \'' . $slug . '_change_option_defaults\', 20 );';
		}
		if ( ! empty( $demo_name ) && ! empty ( $demo_image ) ) {
			$demo_slug = str_replace( '-', '_', sanitize_title_with_dashes( $demo_name ) );
			$demo_brand_logo = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_brand_image', '' );
			$demo_brand_name = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_brand_name', 'Pro Design' );
			$frontpage_id = get_option( 'page_on_front' );
			$blog_id = get_option( 'page_for_posts' );
			$demoext = pathinfo( $demo_image, PATHINFO_EXTENSION );
			$home_title = ( $frontpage_id ? get_the_title( $frontpage_id ) : '' );
			$blog_title = ( $blog_id ? get_the_title( $blog_id ) : '' );
			$output .= '
/**
 * Setup Child Theme Starter
 *
 * @param array $data registered custom templates.
 * @return array
 */
function ' . $slug . '_child_add_starter_templates( $data ) {
	$data[\'' . $demo_slug . '\'] = array(
		\'slug\'                => \'' . $demo_slug . '\',
		\'name\'                => \'' . $demo_name . '\',
		\'local_content\'       => get_stylesheet_directory() . \'/starter/content.xml\',
		\'local_widget_data\'   => get_stylesheet_directory() . \'/starter/widget_data.json\',
		\'local_theme_options\' => get_stylesheet_directory() . \'/starter/theme_options.json\',
		\'url\'                 => \'' . $demo_url . '\',
		\'image\'               => get_stylesheet_directory_uri() . \'/starter/preview.' . $demoext . '\',
		\'ecommerce\'           => ' . ( class_exists( 'woocommerce' ) ? 'true' : 'false' ) . ',
		\'homepage\'            => \'' . $home_title . '\',
		\'blogpage\'            => \'' . $blog_title . '\',
		\'type\'                => \'' . ( class_exists( 'Elementor\Plugin' ) ? 'elementor' : 'blocks' ) . '\',
		\'plugins\'             => array(
			';
			foreach ( $plugin_array as $value ) {
				$output .= "'" . $value . "',";
			}
			$output .= '
		),
		\'menus\'       => array(
			' . $menus_output . '
		),
	);
	return $data;
}
add_filter( \'kadence_starter_templates_custom_array\', \'' . $slug . '_child_add_starter_templates\', 20 );

add_filter( \'kadence_custom_child_starter_templates_enable\', \'__return_true\' );

/**
 * Setup Child Theme Starter Brand
 *
 * @param string $name the brand name.
 * @return string
 */
function ' . $slug . '_child_add_starter_templates_name( $name ) {
	return \'' . $demo_brand_name . '\';
}
add_filter( \'kadence_custom_child_starter_templates_name\', \'' . $slug . '_child_add_starter_templates_name\', 20 );
';
			if ( ! empty( $demo_brand_logo ) ) {
				$demologoext = pathinfo( $demo_brand_logo, PATHINFO_EXTENSION );
				$output .= '
/**
 * Setup Child Theme Starter Logo
 *
 * @param string $url the file url.
 * @return string
 */
function ' . $slug . '_child_add_starter_templates_logo( $url ) {
	return get_stylesheet_directory_uri() . \'/starter/logo.' . $demologoext . '\';
}
add_filter( \'kadence_custom_child_starter_templates_logo\', \'' . $slug . '_child_add_starter_templates_logo\', 20 );
';
			}
		}
		$cloud_url = kadence_build_child_get_option( 'kadence_build_child_cloud_config', 'kct_cloud_url', '' );
		$cloud_key = kadence_build_child_get_option( 'kadence_build_child_cloud_config', 'kct_cloud_key', '' );
		$cloud_name = kadence_build_child_get_option( 'kadence_build_child_cloud_config', 'kct_cloud_name', $child_name );
		if ( ! empty( $cloud_url ) && ! empty( $cloud_key ) ) {
			$output .= '
/**
 * Setup Child Theme Cloud Library
 *
 * @param array $libraries the cloud libraries.
 * @return array
 */
function ' . $slug . '_child_add_cloud_library( $libraries ) {
	$libraries[] = array(
		\'slug\' => \'' . $slug . '_child_library' . '\',
		\'title\' => \'' . $cloud_name . '\',
		\'key\' => \'' . $cloud_key . '\',
		\'url\' => \'' . $cloud_url . '\',
	);
	return $libraries;
}
add_filter( \'kadence_blocks_custom_prebuilt_libraries\', \'' . $slug . '_child_add_cloud_library\', 20 );
';
		}
		return $output;
	}
	/**
	 * Loads admin style sheets and scripts
	 */
	public function scripts() {
		wp_register_script( 'kadence_child_generator', KADENCE_BUILD_CHILD_URL . 'assets/child-gen.js', array( 'jquery' ), '1.0.2', true );
		wp_localize_script(
			'kadence_child_generator',
			'kadence_gen',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'kadence-child-ajax-verification' ),
			)
		);
	}
	/**
	 * Outputs theme zip.
	 */
	public function export_callback() {

		check_ajax_referer( 'kadence-child-ajax-verification', 'security' );

		global $wpdb;
		// Clear the temp folder.
		array_map( 'unlink', glob( KADENCE_BUILD_CHILD_PATH . '/temp/*' ) );
		//$filesystem = $this->get_filesystem();
		$child_name = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_name', 'Pro Design' );
		$child_screenshot = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_screenshot', '' );
		$demo_name = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_name', 'Pro Design' );
		$demo_image = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_image', '' );
		$demo_brand_logo = kadence_build_child_get_option( 'kadence_build_child_demo_config', 'kct_template_brand_image', '' );

		$result = array();
		$child_theme_slug = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_slug', '' );
		if ( ! empty( $child_theme_slug ) ) {
			$child_slug = str_replace( '-', '_', $this->slugify( $child_theme_slug ) );
		} else {
			$child_slug = str_replace( '-', '_', $this->slugify( $child_name ) );
		}
		if ( ! empty( $child_theme_slug ) ) {
			$child_file = $this->slugify( $child_theme_slug );
		} else {
			$child_file = $this->slugify( $child_name );
		}
		$child_folder = $child_file . '/';
		$zip = new ZipArchive();
		$filename = $child_file . '.zip';
		if ( $zip->open( KADENCE_BUILD_CHILD_PATH . '/temp/' . $filename, ZipArchive::CREATE ) !== TRUE ) {
			wp_send_json_error( 'Can not create file' );
		}
		$ext = pathinfo( $child_screenshot, PATHINFO_EXTENSION );
		$zip->addFromString( $child_folder . 'screenshot.' . $ext, $this->get_file_contents( $child_screenshot ) );
		$zip->addFromString( $child_folder . 'style.css', $this->get_stylesheet() );
		$zip->addFromString( $child_folder . 'functions.php', $this->get_php() );
		if ( ! empty( $demo_name ) && ! empty ( $demo_image ) ) {
			$demoext = pathinfo( $demo_image, PATHINFO_EXTENSION );
			$zip->addFromString( $child_folder . 'starter/preview.' . $demoext, $this->get_file_contents( $demo_image ) );
			if ( ! empty( $demo_brand_logo ) ) {
				$demologoext = pathinfo( $demo_brand_logo, PATHINFO_EXTENSION );
				$zip->addFromString( $child_folder . 'starter/logo.' . $demologoext, $this->get_file_contents( $demo_brand_logo ) );
			}
			require_once( ABSPATH . 'wp-admin/includes/export.php' );
			if ( function_exists( 'export_wp' ) ) {
				ob_start();
				export_wp();
				$export = ob_get_clean();
				$zip->addFromString( $child_folder . 'starter/content.xml', $export );
			}
			$zip->addFromString( $child_folder . 'starter/theme_options.json', $this->export_theme_data() );
			$zip->addFromString( $child_folder . 'starter/widget_data.json', $this->export_widget_data() );
		}

		$zip->close();
		$output = array(
			'response' => true,
			'url' => KADENCE_BUILD_CHILD_URL . 'temp/' . $filename,
		);
		wp_send_json( $output );
	}
	/**
	 * Return the contents of a file.
	 */
	public function get_file_contents( $url ) {
		$file_data = wp_remote_retrieve_body(
			wp_safe_remote_get(
				$url,
				array(
					'timeout'   => '60',
					'sslverify' => false,
				)
			)
		);
		// Empty file content?
		if ( empty( $file_data ) ) {
			// Could be empty because of local or none ssl. Lets try the WP file system.
			$path = $this->url_to_path( $url );
			if ( $path ) {
				$wp_filesystem = $this->get_filesystem();
				$file_data = $wp_filesystem->get_contents( $path );
				if ( is_wp_error( $file_data ) || empty( $file_data ) ) {
					return '';
				}
				return $file_data;
			}
			return '';
		}
		return $file_data;
	}
	/**
	 * Get path from url
	 */
	public function url_to_path( $url ) {
		$parsed_url = parse_url( $url );
		if ( empty( $parsed_url['path'] ) ) {
			return false;
		}
		$file = ABSPATH . ltrim( $parsed_url['path'], '/' );
		if ( file_exists( $file ) ) {
			return $file;
		}
		return false;
	}
	/**
	 * Build Style Sheet
	 */
	public function get_stylesheet() {
		$name   = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_name', 'Pro Design' );
		$child_theme_version = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_theme_version', '1.0' );
		$child_author = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_author', 'Pro Author' );
		$child_description = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_description', 'Child Theme Description' );
		$child_theme_url = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_theme_url', 'n/a' );
		$child_author_url = kadence_build_child_get_option( 'kadence_build_child_theme_config', 'kct_author_url', 'n/a' );
		$custom_css = '';
		if ( is_child_theme() ) {
			$child_theme_styles = $this->get_file_contents( get_stylesheet_directory_uri() . '/style.css' );
			$child_theme_styles = str_replace( "/*","_COMSTART", $child_theme_styles);
			$child_theme_styles = str_replace( "*/","COMEND_", $child_theme_styles );
			$child_theme_styles = preg_replace( "/_COMSTART.*?COMEND_/s", "", $child_theme_styles );
			if ( ! empty( $child_theme_styles ) ) {
				$custom_css .= $child_theme_styles;
			}
		}
		$custom_css .= wp_get_custom_css();
		$output = "/*
Theme Name:     {$name}
Theme URI:      {$child_theme_url}
Template:       kadence
Author:         {$child_author}
Author URI:     {$child_author_url}
Description:    {$child_description}
Version:        {$child_theme_version}
License:        GNU General Public License v3.0 (or later)
License URI:    https://www.gnu.org/licenses/gpl-3.0.html
*/
{$custom_css}
";
		return $output;
	}
	/**
	 * Get the filesystem.
	 *
	 * @access protected
	 * @return WP_Filesystem
	 */
	protected function get_filesystem() {
		global $wp_filesystem;

		// If the filesystem has not been instantiated yet, do it here.
		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}
			WP_Filesystem();
		}
		return $wp_filesystem;
	}
	/**
	 * Export Theme settings.
	 *
	 * @access private
	 * @param object $wp_customize An instance of WP_Customize_Manager.
	 * @return void
	 */
	private function export_theme_data() {
		$template = 'kadence';
		$mods     = get_theme_mods();
		$data     = array(
			'template' => $template,
			'mods'     => $mods ? $mods : array(),
			'options'  => array(),
		);
		if ( get_option( 'kadence_global_palette' ) ) {
			$data['options']['kadence_global_palette'] = get_option( 'kadence_global_palette' );
		}
		if ( class_exists( 'woocommerce' ) ) {
			if ( get_option( 'woocommerce_catalog_columns' ) ) {
				$data['options']['woocommerce_catalog_columns'] = get_option( 'woocommerce_catalog_columns' );
			}
			if ( get_option( 'woocommerce_catalog_rows' ) ) {
				$data['options']['woocommerce_catalog_rows'] = get_option( 'woocommerce_catalog_rows' );
			}
			if ( get_option( 'woocommerce_single_image_width' ) ) {
				$data['options']['woocommerce_single_image_width'] = get_option( 'woocommerce_single_image_width' );
			}
			if ( get_option( 'woocommerce_thumbnail_image_width' ) ) {
				$data['options']['woocommerce_thumbnail_image_width'] = get_option( 'woocommerce_thumbnail_image_width' );
			}
			if ( get_option( 'woocommerce_thumbnail_cropping' ) ) {
				$data['options']['woocommerce_thumbnail_cropping'] = get_option( 'woocommerce_thumbnail_cropping' );
			}
			if ( get_option( 'woocommerce_thumbnail_cropping_custom_width' ) ) {
				$data['options']['woocommerce_thumbnail_cropping_custom_width'] = get_option( 'woocommerce_thumbnail_cropping_custom_width' );
			}
			if ( get_option( 'woocommerce_thumbnail_cropping_custom_height' ) ) {
				$data['options']['woocommerce_thumbnail_cropping_custom_height'] = get_option( 'woocommerce_thumbnail_cropping_custom_height' );
			}
		}
		// Serialize the export data.
		return serialize( $data );
	}
	/**
	 * Available widgets
	 *
	 * Gather site's widgets into array with ID base, name, etc.
	 * Used by export and import functions.
	 */
	public function get_available_widgets() {
		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {
			// No duplicates.
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}
		}

		return apply_filters( 'wie_available_widgets', $available_widgets );
	}

	/**
	 * Generate widget export data (Based on Widget Import Export Plugin)
	 *
	 * @since 0.1
	 * @return string Export widget contents
	 */
	public function export_widget_data() {
		// Get all available widgets site supports.
		$available_widgets = $this->get_available_widgets();

		// Get all widget instances for each widget.
		$widget_instances = array();

		// Loop widgets.
		foreach ( $available_widgets as $widget_data ) {
			// Get all instances for this ID base.
			$instances = get_option( 'widget_' . $widget_data['id_base'] );

			// Have instances.
			if ( ! empty( $instances ) ) {
				// Loop instances.
				foreach ( $instances as $instance_id => $instance_data ) {
					// Key is ID (not _multiwidget).
					if ( is_numeric( $instance_id ) ) {
						$unique_instance_id                      = $widget_data['id_base'] . '-' . $instance_id;
						$widget_instances[ $unique_instance_id ] = $instance_data;
					}
				}
			}
		}

		// Gather sidebars with their widget instances.
		$sidebars_widgets          = get_option( 'sidebars_widgets' );
		$sidebars_widget_instances = array();
		foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
			// Skip inactive widgets.
			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			// Skip if no data or not an array (array_version).
			if ( ! is_array( $widget_ids ) || empty( $widget_ids ) ) {
				continue;
			}

			// Loop widget IDs for this sidebar.
			foreach ( $widget_ids as $widget_id ) {
				// Is there an instance for this widget ID?
				if ( isset( $widget_instances[ $widget_id ] ) ) {
					// Add to array.
					$sidebars_widget_instances[ $sidebar_id ][ $widget_id ] = $widget_instances[ $widget_id ];
				}
			}
		}

		// Filter pre-encoded data.
		$data = apply_filters( 'wie_unencoded_export_data', $sidebars_widget_instances );

		// Encode the data for file contents.
		$encoded_data = wp_json_encode( $data );

		// Return contents.
		return apply_filters( 'wie_generate_export_data', $encoded_data );
	}
}
Kadence_Build_Child_Defaults::get_instance();
