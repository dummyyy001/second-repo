<?php
/**
 * Class file to check for active license
 *
 * @package Kadence Plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once KADENCE_BUILD_CHILD_PATH . 'kadence-activation/class-kadence-plugin-api-manager.php';
if ( is_multisite() ) {
	$show_local_activation = apply_filters( 'kadence_activation_individual_multisites', false );
	if ( $show_local_activation ) {
		if ( 'Activated' === get_option( 'kadence_build_child_defaults_activation' ) ) {
			$kadence_build_child_updater = Kadence_Update_Checker::buildUpdateChecker( 'https://kernl.us/api/v1/updates/600d1557762f3a595ee60ae7/', KADENCE_BUILD_CHILD_PATH . 'kadence-build-child-defaults.php', 'kadence-build-child-defaults' );
		}
	} else {
		if ( 'Activated' === get_site_option( 'kadence_build_child_defaults_activation' ) ) {
			$kadence_build_child_updater = Kadence_Update_Checker::buildUpdateChecker( 'https://kernl.us/api/v1/updates/600d1557762f3a595ee60ae7/', KADENCE_BUILD_CHILD_PATH . 'kadence-build-child-defaults.php', 'kadence-build-child-defaults' );
		}
	}
} elseif ( 'Activated' === get_option( 'kadence_build_child_defaults_activation' ) ) {
	$kadence_build_child_updater = Kadence_Update_Checker::buildUpdateChecker( 'https://kernl.us/api/v1/updates/600d1557762f3a595ee60ae7/', KADENCE_BUILD_CHILD_PATH . 'kadence-build-child-defaults.php', 'kadence-build-child-defaults' );
}
