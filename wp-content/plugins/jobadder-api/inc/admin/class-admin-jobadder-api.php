<?php
/**
 * Admin Jobadder API Class
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || die( 'You are not allowed to access.' ); // Terminate if accessed directly

class BH2OAJAA {
    /**
	 * Constructor.
	 */
	public function __construct() {
    	// Enqueue scripts
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
    
    	// Settings Init
        add_action( 'admin_init', [$this, 'settings_init'] );
        
        // Add options page in admin menu
        add_action( 'admin_menu', [$this, 'options_page'] );
        
        // Auth ajax action
        add_action( 'wp_ajax_get_jobadder_api_auth_ready_url', [$this, 'ajax_get_jobadder_api_auth_ready_url'] );
        
        add_action( 'admin_init', [$this, 'save_access_token_response'] );

        add_action( 'bh2ojaa_refresh_access_token', [$this, 'refresh_access_token'] );

        add_action( 'admin_head', [$this, 'add_import_action'] );

        // Initialize batch process
        add_action( 'wp_batch_processing_init', [$this, 'batch_processing_init'], 15, 1 );

        // Refresj jobs
        add_action( 'bh2ojaa_refresh_job_ads', [$this, 'refresh_job_ads'] );
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
    	wp_enqueue_style( 'bh2ojaa-admin-style', plugin_dir_url( BH2OJAA_PLUGIN_FILE ) . 'assets/css/bh2ojaa-admin.css', array(), BH2OJAA_PLUGIN_VERSION );
    	wp_enqueue_script( 'bh2ojaa-admin-script', plugin_dir_url( BH2OJAA_PLUGIN_FILE ) . 'assets/js/bh2ojaa-admin.js', array( 'jquery' ), BH2OJAA_PLUGIN_VERSION, true );
        
        wp_localize_script( 'bh2ojaa-admin-script', 'wp_obj', array(
        	'ajax_url' 		=> admin_url( 'admin-ajax.php' ),
            'admin_images'	=> admin_url( 'images' )
        ) );
    }
    
    /**
     * Settings Init
     */
    public function settings_init() {
    	// Register a new setting
    	register_setting( 'bh2ojaa_options', 'bh2ojaa_options' );
        
        // Register a new section
        add_settings_section(
            'bh2ojaa_options_init_section',
            __( 'Jobadder API', 'bh2ojaa' ),
            [$this, 'bh2ojaa_options_init_section_callback'],
            'bh2ojaa_options'
        );
        
        // Register fields
        $fields = array(
        	array(
            	'id' 		=> 'bh2ojaa_options_client_id',
                'title' 	=> __( 'Client ID', 'bh2ojaa' ),
                'callback'	=> [$this, 'bh2ojaa_options_text_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                	'label'		=> __( 'Client ID', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_client_id',
                    'class'		=> ''
                )
            ),
            array(
            	'id' 		=> 'bh2ojaa_options_client_secret',
                'title' 	=> __( 'Client Secret', 'bh2ojaa' ),
                'callback'	=> [$this, 'bh2ojaa_options_password_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                	'label'		=> __( 'Client Secret', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_client_secret',
                    'class'		=> ''
                )
            ),
            array(
            	'id' 		=> 'bh2ojaa_options_auth',
                'title' 	=> '',
                'callback'	=> [$this, 'bh2ojaa_options_button_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                	'label'		=> __( 'Authorize', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_auth',
                    'class'		=> ''
                )
            ),
            array(
            	'id' 		=> 'bh2ojaa_options_access_token',
                'title' 	=> __( 'Access Token', 'bh2ojaa' ),
                'callback'	=> [$this, 'bh2ojaa_options_text_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                	'label'		=> __( 'Access Token', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_access_token',
                    'class'		=> ''
                )
            ),
            array(
            	'id' 		=> 'bh2ojaa_options_refresh_token',
                'title' 	=> __( 'Refresh Token', 'bh2ojaa' ),
                'callback'	=> [$this, 'bh2ojaa_options_text_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                	'label'		=> __( 'Refresh Token', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_refresh_token',
                    'class'		=> ''
                )
            ),
            array(
                'id' 		=> 'bh2ojaa_options_board_id',
                'title' 	=> __( 'Board ID', 'bh2ojaa' ),
                'callback'	=> [$this, 'bh2ojaa_options_text_callback'],
                'page'		=> 'bh2ojaa_options',
                'section'	=> 'bh2ojaa_options_init_section',
                'args'		=> array(
                    'label'		=> __( 'Board ID', 'bh2ojaa' ),
                    'label_for'	=> 'bh2ojaa_options_board_id',
                    'class'		=> ''
                )
            )
        );
        
        foreach ( $fields as $field ) {
        	add_settings_field(
            	$field['id'],
                $field['title'],
                $field['callback'],
                $field['page'],
                $field['section'],
                $field['args']
            );
        }
    }
    
    /**
     * The Jobadder API init section callback
     *
     * @param array $args  The settings array, defining title, id, callback
     */
    public function bh2ojaa_options_init_section_callback( $args ) {
    	?>
    	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Configure your Jobadder API here.' ); ?></p>
    	<?php
    }
    
    /**
     * Text field callback
     *
     * @param array $args
     */
    public function bh2ojaa_options_text_callback( $args ) {
        // Get the value of the setting we've registered with register_setting()
    	$options    = get_option( 'bh2ojaa_options' );
        $value      = isset( $options[$args['label_for']] ) ? $options[$args['label_for']] : '';
        ?>
        <input name="bh2ojaa_options[<?php echo $args['label_for']; ?>]" type="text" id="<?php echo $args['label_for']; ?>" value="<?php echo $value; ?>" class="regular-text">
        <?php
    }
    
    /**
     * Password field callback
     *
     * @param array $args
     */
    public function bh2ojaa_options_password_callback( $args ) {
        // Get the value of the setting we've registered with register_setting()
    	$options    = get_option( 'bh2ojaa_options' );
        $value      = isset( $options[$args['label_for']] ) ? $options[$args['label_for']] : '';
        ?>
        <input name="bh2ojaa_options[<?php echo $args['label_for']; ?>]" type="password" id="<?php echo $args['label_for']; ?>" value="<?php echo $value; ?>" class="regular-text">
        <?php
    }
    
    /**
     * Button field callback
     *
     * @param array $args
     */
    public function bh2ojaa_options_button_callback( $args ) {
    	?>
        <button name="bh2ojaa_options[<?php echo $args['label_for']; ?>]" id="<?php echo $args['label_for']; ?>" class="button button-primary"><?php echo $args['label']; ?></button>
        <?php
    }
    
    /**
     * Add options page in admin menu
     */
    public function options_page() {
        add_options_page(
            'Jobadder API Options',
            'Jobadder API Options',
            'manage_options',
            'bh2ojaa_options',
            [$this, 'options_page_callback']
        );
    }
    
    /**
     * Options page callback
     */
    public function options_page_callback() {
        // check user capabilities
        if ( !current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "bh2ojaa_options"
                settings_fields( 'bh2ojaa_options' );
                // output setting sections and their fields
                // (sections are registered for "bh2ojaa_options", each field is registered to a specific section)
                do_settings_sections( 'bh2ojaa_options' );
                // output save settings button
                submit_button( 'Save' );
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Auth ajax action
     */
    public function ajax_get_jobadder_api_auth_ready_url() {
    	$jobadder_api 	= new BH2OJAA();
        $redirect_uri	= admin_url( 'options-general.php?page=bh2ojaa_options' );
        
        wp_send_json( $jobadder_api->get_auth_ready_url( $redirect_uri ) );
        
        wp_die();
    }
    
    /**
     * Get API access token
     */
    public function save_access_token_response() {
    	global $pagenow;
    	if ( 'options-general.php' === $pagenow && isset( $_GET['page'] ) ) {
        	if ( $_GET['page'] == 'bh2ojaa_options' && isset( $_GET['code'] ) ) {
                $options        = get_option( 'bh2ojaa_options' );
                $jobadder_api 	= new BH2OJAA();
                $auth_code	    = $_GET['code'];
                $redirect_uri	= admin_url( 'options-general.php?page=bh2ojaa_options' );

                $res	        = $jobadder_api->get_access_token( $auth_code, $redirect_uri );
                $res            = json_decode( $res );

                $this->update_access_token_options( $res );

                wp_redirect( $redirect_uri );

                exit;
            }
        }
    }

    /**
     * Refresh access token
     * 
     * @param string $refresh_token
     */
    public function refresh_access_token( $refresh_token ) {
        $options        = get_option( 'bh2ojaa_options' );
        $jobadder_api 	= new BH2OJAA();

        $res	        = $jobadder_api->get_access_token_by_refresh_token( $refresh_token );
        $res            = json_decode( $res );

        $this->update_access_token_options( $res );
    }

    /**
     * Update access token options from response
     * 
     * @param object $res
     */
    private function update_access_token_options( $res ) {
        $options    = get_option( 'bh2ojaa_options' );
        if ( !property_exists( $res, 'error' ) ) {
            if ( property_exists( $res, 'access_token' ) ) {
                $access_token   = $res->access_token;
                $expires_in     = $res->expires_in;
                $token_type     = $res->token_type;
                $refresh_token  = $res->refresh_token;
                $api            = $res->api;

                $options['bh2ojaa_options_access_token']    = $access_token;
                $options['bh2ojaa_options_token_type']      = $token_type;
                $options['bh2ojaa_options_refresh_token']   = $refresh_token;
                $options['bh2ojaa_options_api']             = $api;

                update_option( 'bh2ojaa_options', $options );

                if ( $expires_in == HOUR_IN_SECONDS ) {
                    $recurrence = 'hourly';
                } elseif ( $expires_in == ( 12 * HOUR_IN_SECONDS ) ) {
                    $recurrence = 'twicedaily';
                } elseif ( $expires_in == DAY_IN_SECONDS ) {
                    $recurrence = 'daily';
                } elseif ( $expires_in == WEEK_IN_SECONDS ) {
                    $recurrence = 'weekly';
                } else {
                    $recurrence = 'monthly';
                }

                $hook = 'bh2ojaa_refresh_access_token';
                $args = array( $refresh_token );

                if ( !wp_next_scheduled( $hook, $args ) ) {
                    wp_schedule_event( time() + $expires_in, $recurrence, $hook, $args );
                } else {
                    wp_clear_scheduled_hook( $hook, $args );
                    wp_reschedule_event( time() + $expires_in, $recurrence, $hook, $args );
                }
            }
        }
    }

    /**
     * Add import action button in head
     */
    public function add_import_action() {
        global $current_screen;

        if ( 'edit-jobadder_job_ads' !== $current_screen->id ) return;

        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $($(".wrap h1")[0]).append("<a title='Import Job Ads from API' href='<?php echo admin_url( 'admin.php?page=dg-batches&action=view&id=jobadder_job_ads' ); ?>' class='page-title-action'>Import from API</a>")
            })
        </script>
        <?php
    }

    /**
     * Initialize batch process
     */
    public function batch_processing_init() {
        require_once( plugin_dir_path( __FILE__ ) . 'class-admin-jobadder-batch.php' );

		$batch = new BH2OAJAA_Batch();
		WP_Batch_Processor::get_instance()->register( $batch );
    }

    /**
     * Refresh job ads
     */
    public function refresh_job_ads() {
        $ads    = bh2ojaa_get_jobadder_job_ads();
        
        if ( !is_wp_error( $ads ) ) {
            $ad_ids = array();

            foreach ( $ads->items as $ad ) {
                $ad_id      = $ad->adId;
                $ad         = bh2ojaa_get_jobadder_job_ad( $ad_id );
                bh2ojaa_insert_job_ad( $ad );
                $ad_ids[]   = $ad_id;
            }
    
            $posts = get_posts( array(
                'post_type'     => 'jobadder_job_ads',
                'numberposts'   => -1
            ) );
    
            foreach ( $posts as $post ) {
                $ad_id = get_post_meta( $post->ID, 'jobAdId', true );
    
                if ( !in_array( $ad_id, $ad_ids ) ) {
                    //wp_mail
                    wp_delete_post( $post->ID );
                }
            }
        }
        //wp_mail
    }
}

new BH2OAJAA();