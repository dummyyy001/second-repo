<?php

/**
 * All the popup goodness and basics.
 *
 * Contains a bunch of helper methods as well.
 *
 * @package    WPPopups
 * @author     WPPopups
 * @since 2.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2016, WP Popups LLC
 */
class WPPopups_Content_Templates {

	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'registerPostType' ] );

		// Column Name
		add_filter( 'manage_wppopups-templates_posts_columns', [ $this, 'columnName' ] );

		// Column Value
		add_action( 'manage_wppopups-templates_posts_custom_column', [ $this, 'columnValue' ], 10, 2);

		// Create shortcode
		add_shortcode( 'wppopup-template', [ $this, 'createShortcode' ] );
	}

	/**
	 * Registers the custom post type to be used for popups.
	 *
	 * @since 2.0.0
	 */
	public function registerPostType(): void {

		// Custom post type arguments, which can be filtered if needed
		$args = [
			'labels'              => [
				'name'      => esc_html__( 'Content Templates', 'wp-popups-lite' ),
				'menu_name' => esc_html__( 'Content Templates', 'wp-popups-lite' ),
			],
			'public'              => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => [ 'title', 'editor' ],
			'can_export'          => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'	  => true,
			'menu_position'       => 5,
		];

		// Register the post type
		register_post_type( 'wppopups-templates', $args );

		// Refresh
		flush_rewrite_rules();
	}


	/**
	 * Column custom post type
	 * @param  array  $cols 
	 * @return array
	 */
	public function columnName( array $cols = [] ): array {

		$newCols = [];

		foreach ( $cols as $keyColumn => $valueColumn ) {	
			$newCols[ $keyColumn ] = $valueColumn;

			if( $keyColumn == 'title' ) {
				$newCols['shortcode'] = esc_html__( 'Shortcode', 'wppopups-lite' );
			}
		}

		return $newCols;
	}

	/**
	 * Column value custom post type
	 * @param  string $col
	 * @param  int    $postID 
	 * @return mixed
	 */
	public function columnValue( string $col, int $postID ): void {
		if ( $col !== 'shortcode' ) {
			return;
		}

		printf( '<pre>[wppopup-template id="%d"]</pre>', $postID );
	}


	/**
	 * Create shortcode
	 * @param  array  $atts
	 * @return mixed
	 */
	public function createShortcode( array $atts ): mixed {
		if ( ! isset( $atts['id'] ) ) {
			return false;
		}

		$post = get_post( absint( $atts['id'] ) );

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		return do_blocks( $post->post_content );
	}
}

new WPPopups_Content_Templates();