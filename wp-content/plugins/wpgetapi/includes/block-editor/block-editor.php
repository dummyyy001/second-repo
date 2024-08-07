<?php

/**
 * Register WPGetAPI Gutenberg block and Enqueue localize script data for block.
 *
 * @return void
 */

add_action( 'init', 'wpgetapi_init_block_editor_assets', 10, 0 );

function wpgetapi_init_block_editor_assets() {

	$assets = array();

	$asset_file = WPGETAPIDIR . 'includes/block-editor/index-asset.php';

	if ( file_exists( $asset_file ) ) {
		$assets = include $asset_file;
	}

	$assets = wp_parse_args(
		$assets,
		array(
			'dependencies' => array(
				'wp-api-fetch',
				'wp-block-editor',
				'wp-blocks',
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-url',
			),
			'version'      => WPGETAPIVERSION,
		)
	);

	wp_enqueue_script(
		'wpgetapi-endpoint-selector-editor-script',
		WPGETAPIURL . 'includes/block-editor/index.js',
		$assets['dependencies'],
		$assets['version']
	);

	wp_set_script_translations(
		'wpgetapi-endpoint-selector-editor-script',
		'wpgetapi'
	);

	$attributes = apply_filters(
		'wpgetapi_gutenberg_attributes',
		array(
			'api_id'      => array( 'type' => 'string' ),
			'endpoint_id' => array( 'type' => 'string' ),
			'debug'       => array( 'type' => 'string' ),
		)
	);

	$select_options = apply_filters(
		'wpgetapi_gutenberg_select_options',
		array(
			'format'      => array(
				array(
					'label' => 'Available in PRO plugin',
					'value' => '',
				),
			),
			'html_tags'   => array(
				array(
					'label' => 'Available in PRO plugin',
					'value' => '',
				),
			),
			'html_labels' => array(
				array(
					'label' => 'Available in PRO plugin',
					'value' => '',
				),
			),
		)
	);

	$data           = array();
	$count_api      = 0;
	$count_endpoint = 0;
	$apis           = get_option( 'wpgetapi_setup' );

	if ( $apis ) {
		foreach ( $apis as $i => $api ) {
			if ( $api ) {
				foreach ( $api as $i2 => $item ) {
					$endpoints = get_option( 'wpgetapi_' . $item['id'] );
					if ( isset( $endpoints['endpoints'] ) ) {
						$data['apis'][ $count_api ]['api_id'] = $item['id'];
						foreach ( $endpoints['endpoints'] as $i3 => $endpoint ) {
							$data['endpoints'][ $count_endpoint ]['id']          = $item['id'] . '_' . $endpoint['id'];
							$data['endpoints'][ $count_endpoint ]['api_id']      = $item['id'];
							$data['endpoints'][ $count_endpoint ]['endpoint_id'] = $endpoint['id'];
							$data['endpoints'][ $count_endpoint ]['endpoint']    = $endpoint['endpoint'];
							++$count_endpoint;
						}
						++$count_api;
					}
				}
			}
		}
	}

	register_block_type(
		WPGETAPIDIR . 'includes/block-editor',
		array(
			'editor_script'   => 'wpgetapi-block-editor',
			'render_callback' => 'wpgetapi_get_gutenberg_html',
			'attributes'      => $attributes,
		)
	);

	wp_localize_script(
		'wpgetapi-endpoint-selector-editor-script',
		'wpgetapi_block_editor',
		array(
			'logo_url'       => WPGETAPIURL . 'assets/img/wpgetapi-icon.png',
			'endpoints'      => isset( $data['endpoints'] ) ? $data['endpoints'] : '',
			'apis'           => isset( $data['apis'] ) ? $data['apis'] : '',
			'attributes'     => $attributes,
			'select_options' => $select_options,
		),
		'before'
	);
}

/**
 * Get form HTML to display in a WPGetAPI Gutenberg block.
 *
 * @since 2.0.0
 *
 * @param array $attr Attributes passed by WPGetAPI Gutenberg block.
 *
 * @return string
 */
function wpgetapi_get_gutenberg_html( $attr, $content ) {

	$api_id      = ! empty( $attr['api_id'] ) ? $attr['api_id'] : 0;
	$endpoint_id = ! empty( $attr['endpoint_id'] ) ? $attr['endpoint_id'] : 0;

	if ( empty( $api_id ) || empty( $endpoint_id ) ) {
		return 'no';
	}

	ob_start();

	/**
	 * Fires before Gutenberg block output.
	 *
	 * @since 1.5.8.2
	 */
	do_action( 'wpgetapi_gutenberg_block_before' );

	$debug              = ! empty( $attr['debug'] ) ? $attr['debug'] : 'false';
	$format             = ! empty( $attr['format'] ) ? $attr['format'] : '';
	$html_tag           = ! empty( $attr['html_tag'] ) ? $attr['html_tag'] : '';
	$html_labels        = ! empty( $attr['html_labels'] ) ? $attr['html_labels'] : '';
	$keys               = ! empty( $attr['keys'] ) ? $attr['keys'] : '';
	$query_variables    = ! empty( $attr['query_variables'] ) ? $attr['query_variables'] : '';
	$endpoint_variables = ! empty( $attr['endpoint_variables'] ) ? $attr['endpoint_variables'] : '';

	$shortcode = sprintf(
		'[wpgetapi_endpoint 
            api_id="' . $api_id . '" 
            endpoint_id="' . $endpoint_id . '" 
            debug="' . $debug . '" 
            format="' . $format . '"
            html_tag="' . $html_tag . '"
            html_labels="' . $html_labels . '"
            keys="' . $keys . '"
            query_variables="' . $query_variables . '"
            endpoint_variables="' . $endpoint_variables . '"
        ]'
	);

	echo do_shortcode( shortcode_unautop( $shortcode ) );

	/**
	 * Fires after Gutenberg block output.
	 *
	 * @since 1.5.8.2
	 */
	do_action( 'wpgetapi_gutenberg_block_after' );

	$content = ob_get_clean();

	if ( empty( $content ) ) {
		$content = '<div class="components-placeholder"><div class="components-placeholder__label"></div>' .
		'<div class="components-placeholder__fieldset">' .
		esc_html__( 'The data cannot be displayed.', 'wpgetapi' ) .
			'</div></div>';
	}

	/**
	 * Filter Gutenberg block content.
	 *
	 * @since 1.5.8.2
	 *
	 * @param string $content Block content.
	 * @param int    $id      Form id.
	 */
	return apply_filters( 'wpgetapi_gutenberg_block_content', $content, $api_id, $endpoint_id, $attr );
}
