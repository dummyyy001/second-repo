<?php
/**
 * Jobadder CPT
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || die( 'You are not allowed to access.' ); // Terminate if accessed directly


/**
 * Register CPT.
 */
add_action( 'init', 'bh2ojaa_setup_post_type' );
function bh2ojaa_setup_post_type() {
    // Set UI labels for CPT
    $labels = array(
        'name'                => __( 'Job Ads', 'Jobs', 'bh2ojaa' ),
        'singular_name'       => __( 'Job Ad', 'Job', 'bh2ojaa' ),
        'menu_name'           => __( 'Job Ads', 'bh2ojaa' ),
        'parent_item_colon'   => __( 'Parent Job Ad', 'bh2ojaa' ),
        'all_items'           => __( 'All Job Ads', 'bh2ojaa' ),
        'view_item'           => __( 'View Job Ad', 'bh2ojaa' ),
        'add_new_item'        => __( 'Add New Job Ad', 'bh2ojaa' ),
        'add_new'             => __( 'Add New', 'bh2ojaa' ),
        'edit_item'           => __( 'Edit Job Ad', 'bh2ojaa' ),
        'update_item'         => __( 'Update Job Ad', 'bh2ojaa' ),
        'search_items'        => __( 'Search Job Ad', 'bh2ojaa' ),
        'not_found'           => __( 'Not Found', 'bh2ojaa' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'bh2ojaa' ),
    );

    $args = array(
        'label'               => __( 'Job Ad', 'bh2ojaa' ),
        'description'         => __( 'Job Ad description', 'bh2ojaa' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 25,
        'menu_icon'			  => 'dashicons-index-card',
        'can_export'          => true,
        'taxonomies'          => array( 'job_type' ),
        'has_archive'         => false,
        'rewrite'             => array(
            'slug' => 'jobs'
        ),
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'capabilities'        => array(
	        'create_posts'    => 'do_not_allow'
        ),
        'map_meta_cap' => false
    );

    register_post_type( 'jobadder_job_ads', $args );
}

/**
 * Register Custom Taxonomy.
 */
add_action( 'init', 'bh2ojaa_register_custom_taxonomy' );
function bh2ojaa_register_custom_taxonomy() {
    $args = array(
        'label'        => __( 'Job Types', 'bh2ojaa' ),
        'public'       => true,
        'rewrite'      => false,
        'hierarchical' => true
    );

    register_taxonomy( 'job_type', 'jobadder_job_ads', $args );
}

/**
 * Filter the single_template with our custom function
 */
add_filter( 'single_template', 'bh2ojaa_jobs_template' );
function bh2ojaa_jobs_template( $single ) {
    global $post;

    // Checks for single template by post type
    if ( 'jobadder_job_ads' === $post->post_type ) {
        $path = plugin_dir_path( BH2OJAA_PLUGIN_FILE ) . 'views/single-jobadder_job_ads.php';
        if ( file_exists( $path ) ) {
            return $path;
        }
    }

    return $single;
}