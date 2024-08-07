<?php
/**
 * Class to Build the Query Filter Rating Block.
 *
 * @package Kadence Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Build the Query No Results Block.
 *
 * @category class
 */
class Kadence_Blocks_Pro_Filter_Rating_Block extends Kadence_Blocks_Query_Children_Block {
	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Block name within this namespace.
	 *
	 * @var string
	 */
	protected $block_name = 'filter-rating';

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
	 * Builds CSS for block.
	 *
	 * @param array  $attributes the blocks attributes.
	 * @param string $css the css class for blocks.
	 * @param string $unique_id the blocks attr ID.
	 * @param string $unique_style_id the blocks alternate ID for queries.
	 */
	public function build_css( $attributes, $css, $unique_id, $unique_style_id ) {
		$css->set_style_id( 'kb-' . $this->block_name . $unique_style_id );

		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options' );

		$css->render_measure_output( $attributes, 'padding', 'padding', array( 'unit_key' => 'paddingUnit' ) );
		$css->render_measure_output( $attributes, 'margin', 'margin', array( 'unit_key' => 'marginUnit' ) );

		$css->render_border_styles( $attributes, 'borderStyle' );
		$css->render_measure_output( $attributes, 'borderRadius', 'border-radius' );
		$css->render_typography( $attributes, 'typography' );

		// Colors.
		if ( ! empty( $attributes['backgroundType'] ) && 'gradient' == $attributes['backgroundType'] && ! empty( $attributes['gradient'] ) ) {
			$css->add_property( 'background', $attributes['gradient'] );
		} else if ( ! empty( $attributes['background'] ) ) {
			$css->render_color_output( $attributes, 'background', 'background' );
		}
		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options label' );
		if ( ! empty( $attributes['color'] ) ) {
			$css->render_color_output( $attributes, 'color', 'color' );
		}

		// SVG colors
		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options svg' );
		$css->render_color_output( $attributes, 'iconColor', 'fill' );
		$css->render_color_output( $attributes, 'iconStrokeColor', 'stroke' );
		$css->add_property( 'pointer-events', 'none' );

		$width_unit = isset( $attributes['widthUnit'] ) ? $attributes['widthUnit'] : 'px';
		if( !empty( $attributes['width'][0] )) {
			$css->add_property( 'width', $attributes['width'][0] . $width_unit );
			$css->add_property( 'height', $attributes['width'][0] . $width_unit );
		} else {
			$css->add_property( 'height', '25px' );
			$css->add_property( 'width', '25px' );
		}

		if( !empty( $attributes['width'][1] )) {
			$css->set_media_state('tablet');
			$css->add_property( 'width', $attributes['width'][1] . $width_unit );
			$css->add_property( 'height', $attributes['width'][1] . $width_unit );
			$css->set_media_state('desktop');
		}

		if( !empty( $attributes['width'][2] )) {
			$css->set_media_state('mobile');
			$css->add_property( 'width', $attributes['width'][2] . $width_unit );
			$css->add_property( 'height', $attributes['width'][2] . $width_unit );
			$css->set_media_state('desktop');
		}

		// Pressed
		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options .pressed svg' );
		$css->render_color_output( $attributes, 'iconFillColor', 'fill' );

		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options .hover svg' );
		$css->render_color_output( $attributes, 'iconFillColor', 'fill' );

		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options .kbp-ql-rating:hover svg' );
		$css->render_color_output( $attributes, 'iconFillColor', 'fill' );

		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options .kbp-ql-rating:hover' );
		$css->render_color_output( $attributes, 'iconFillColor', 'fill' );
		$css->add_property( 'cursor', 'pointer');

		$css->set_selector( 'body .wp-block-kadence-query.wp-block-kadence-query .wp-block-kadence-query-filter-rating' . $unique_id . ' .rating-options span' );
		$css->add_property( 'display', 'inline-block' );

		return $css->css_output();
	}

	/**
	 * Return dynamically generated HTML for block
	 *
	 * @param array    $attributes The attributes.
	 * @param string   $unique_id The unique id.
	 * @param string   $content The content.
	 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
	 *
	 * @return string
	 */
	public function build_html( $attributes, $unique_id, $content, $block_instance ) {
		$data = $this->do_query();

		$hash = $this->get_hash_from_unique_id( $unique_id );

		$outer_classes = array(
			'kadence-query-filter',
			'wp-block-kadence-query-filter-rating' . $unique_id,
		);
		$outer_classes[] = ! isset( $attributes['showInline'] ) || ( isset( $attributes['showInline'] ) && $attributes['showInline'] ) ? 'inline' : '';
		$wrapper_args = array(
			'class' => implode( ' ', $outer_classes ),
			'data-uniqueid' => $unique_id,
			'data-hash' => $hash,
		);
		$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );

		$label_html = $this->get_label_html( $attributes );

		$filters = $data && ! empty( $data['filters'] ) ? $data['filters'][ $unique_id ] : '';

		return sprintf(
			'<div %s><fieldset class="kadence-filter-wrap">%s<div class="rating-options">%s</div></fieldset></div>',
			$wrapper_attributes,
			$label_html,
			$filters
		);
	}
}

Kadence_Blocks_Pro_Filter_Rating_Block::get_instance();
