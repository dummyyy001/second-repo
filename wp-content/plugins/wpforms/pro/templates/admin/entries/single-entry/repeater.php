<?php
/**
 * Single entry repeater field template.
 *
 * @since 1.8.9
 *
 * @var array                  $field          Field data.
 * @var array                  $form_data      Form data and settings.
 * @var WPForms_Entries_Single $entries_single Single entry object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $field['display'] ) && $field['display'] === 'rows' ) {
	echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'admin/entries/single-entry/repeater-rows',
		[
			'field'          => $field,
			'form_data'      => $form_data,
			'entries_single' => $entries_single,
		],
		true
	);
} else {
	echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'admin/entries/single-entry/repeater-blocks',
		[
			'field'          => $field,
			'form_data'      => $form_data,
			'entries_single' => $entries_single,
		],
		true
	);
}
