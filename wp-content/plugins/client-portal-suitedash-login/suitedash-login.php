<?php
/*
Plugin Name: Client Portal : SuiteDash Direct Login
Plugin URI: https://suitedash.com
Description: Customize your Client Portal experience by providing an easy and seamless login method directly from your WordPress website
Author: SuiteDash :: ONE Dashboard®
Version: 1.8.0
Author URI: https://suitedash.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_Suitedash_Login' ) ) {

    class WP_Suitedash_Login {
        private static $instance = NULL;
        private $load_popup = false;

        public $default_value = array();

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WP_Suitedash_Login();
            return self::$instance;
        }


        function __construct() {

            $this->default_value = array(
                    'form_title'                => '',
                    'registration_link_text'    => __( 'Not a Member? Register Here', 'wp_suitedash_login' ),
                    'login_form_title_color'    => '#44c4e7',
                    'login_form_background'     => '#ffffff',
                    'login_form_color'          => '#666666',
                    'login_button_bgcolor'      => '#44c4e7',
                    'login_button_color'        => '#ffffff',
            );

            if ( ! defined( 'DOING_AJAX' ) && ! is_admin() ) {
                add_shortcode( 'wp_suitedash_login', array( &$this, 'suitedash_login' ) );
                //without popup
                add_shortcode( 'wp_suitedash_login_form', array( &$this, 'suitedash_login_form' ) );
                //filter menu items for trigger displaying login form HTML
                add_filter( 'wp_nav_menu_objects', array( &$this, 'nav_menu_objects' ), 10, 2 );
                //display login form at footer
                add_action( 'wp_footer', array( &$this, 'footer_loading' ) );
            }

            //login form request
            add_action( 'wp_ajax_nopriv_suitedash_request', array( &$this, 'suitedash_request' ) );
            add_action( 'wp_ajax_suitedash_request', array( &$this, 'suitedash_request' ) );

            add_action( 'wp_ajax_suitedash_request_check_status', array( &$this, 'ajax_suitedash_request_check_status' ) );

            //backend filters for displaying custom nav menu type
            add_action( 'load-nav-menus.php', array( &$this, 'nav_menus' ) );
            add_filter( 'manage_nav-menus_columns', array( &$this, 'manage_nav_menus' ), 99 );

            //settings page
            add_action( 'admin_init', array( $this, 'settings_init' ) );
            add_action( 'admin_menu', array( $this, 'add_options_page' ) );

            add_action( 'admin_enqueue_scripts', array( &$this, 'print_scripts' ), 10 );

            //sanitize fields
            add_filter( 'option_suitedash_login', array( &$this, 'pre_option_suitedash_login' ), 99 );
        }

        /**
         * sanitize fields values
         */
        function pre_option_suitedash_login( $value ) {
            if ( isset( $value['form_request'] ) ) {
                $value['form_request'] = sanitize_url( $value['form_request'] );
            }

            if ( isset( $value['registration_url'] ) ) {
                $value['registration_url'] = sanitize_url( $value['registration_url'] );
            }

            if ( isset( $value['registration_link_text'] ) ) {
                $value['registration_link_text'] = sanitize_text_field( $value['registration_link_text'] );
            }

            if ( isset( $value['form_title'] ) ) {
                $value['form_title'] = sanitize_text_field( $value['form_title'] );
            }

            return $value;
        }

        /**
         * Enqueue scripts for login form popup animation
         *
         */
        function user_print_scripts() {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-effects-core' );
            wp_enqueue_script( 'jquery-effects-bounce' );
            wp_enqueue_script( 'jquery-effects-fade' );
            wp_enqueue_script( 'jquery-effects-size' );
        }


        /**
         * Admin scripts
         *
         */
        function print_scripts() {
            global $pagenow;

            if ( ! empty( $pagenow ) && $pagenow == 'options-general.php' && ! empty( $_GET['page'] ) && $_GET['page'] == 'suitedash_login' ) {
                wp_enqueue_script( 'wp-color-picker' );
                wp_enqueue_style( 'wp-color-picker' );

                wp_enqueue_script( 'admin_colorpicker', plugins_url( 'js/admin.colorpicker.js', __FILE__ ), array( 'wp-color-picker' ), false, 1 );

                // Enqueue WordPress media scripts
                wp_enqueue_media();
                // Enqueue custom script that will interact with wp.media
                wp_enqueue_script( 'sd-custom-media-selection', plugins_url( '/js/custom-media-selection.js' , __FILE__ ), array('jquery'), '0.1' );
            }
        }


        /**
         * Init plugin settings
         */
        function settings_init() {
            register_setting( 'suitedash_login', 'suitedash_login' );


            add_settings_section(
                'sd_form_request_section',
                __( 'Connect to your Custom Domain URL', 'wp_suitedash_login' ),
                array( &$this, 'default_section_cb' ),
                'suitedash_login'
            );

            add_settings_section(
                'sd_text_section',
                __( 'Login Form Header', 'wp_suitedash_login' ),
                array( &$this, 'default_section_cb' ),
                'suitedash_login'
            );

            add_settings_section(
                'sd_registration_section',
                __( '‬Registration Settings', 'wp_suitedash_login' ),
                array( &$this, 'default_section_cb' ),
                'suitedash_login'
            );

            add_settings_section(
                'sd_style_section',
                __( 'Customize Login Form Style', 'wp_suitedash_login' ),
                array( &$this, 'default_section_cb' ),
                'suitedash_login'
            );





            /* Connect to your Custom Domain URL Block */
            add_settings_field(
                'form_request',
                __( 'Custom Domain URL', 'wp_suitedash_login' ),
                array( &$this, 'form_request_field' ),
                'suitedash_login',
                'sd_form_request_section',
                array(
                    'label_for' => 'form_request',
                    'class' => 'suitedash_settings_row',
                )
            );

            /* Connection Status */
            add_settings_field(
                'request_status',
                __( 'Connection Status', 'wp_suitedash_login' ),
                array( &$this, 'form_request_status' ),
                'suitedash_login',
                'sd_form_request_section',
                array(
                    'label_for' => 'request_status',
                    'class' => 'suitedash_settings_row',
                )
            );

            add_settings_field(
                'logo_id',
                __( 'Login Form Image', 'wp_suitedash_login' ),
                array( &$this, 'form_logo_field' ),
                'suitedash_login',
                'sd_text_section',
                array(
                    'label_for' => 'logo_id',
                    'class' => 'suitedash_settings_row',
                )
            );

            /* Login Form Texts Block */
            add_settings_field(
                'form_title',
                __( 'Login Form Title', 'wp_suitedash_login' ),
                array( &$this, 'form_text_field' ),
                'suitedash_login',
                'sd_text_section',
                array(
                    'label_for' => 'form_title',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['form_title'],
                )
            );


            /* ‬Registration Settings Block */
            add_settings_field(
                'registration_url',
                __( '‬Registration URL', 'wp_suitedash_login' ),
                array( &$this, 'form_text_field' ),
                'suitedash_login',
                'sd_registration_section',
                array(
                    'label_for' => 'registration_url',
                    'class' => 'suitedash_settings_row',
                )
            );

            add_settings_field(
                'registration_link_text',
                __( '‬Registration Link Text', 'wp_suitedash_login' ),
                array( &$this, 'form_text_field' ),
                'suitedash_login',
                'sd_registration_section',
                array(
                    'label_for' => 'registration_link_text',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['registration_link_text'],
                )
            );

            add_settings_field(
                'registration_open_new_tab',
                __( 'Open in New Tab', 'wp_suitedash_login' ),
                array( &$this, 'form_switch_field' ),
                'suitedash_login',
                'sd_registration_section',
                array(
                    'label_for' => 'registration_open_new_tab',
                    'class' => 'suitedash_settings_row',
                )
            );





            /* Customize Login Form Style Block */
            add_settings_field(
                'login_form_title_color',
                __( 'Login Form Title Text', 'wp_suitedash_login' ),
                array( &$this, 'style_color_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_form_title_color',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['login_form_title_color'],
                )
            );

            add_settings_field(
                'login_form_background',
                __( 'Login Form Background', 'wp_suitedash_login' ),
                array( &$this, 'style_color_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_form_background',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['login_form_background'],
                )
            );

            add_settings_field(
                'login_form_color',
                __( 'Login Form Text', 'wp_suitedash_login' ),
                array( &$this, 'style_color_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_form_color',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['login_form_color'],
                )
            );

            add_settings_field(
                'login_button_bgcolor',
                __( 'Login Button Background', 'wp_suitedash_login' ),
                array( &$this, 'style_color_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_button_bgcolor',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['login_button_bgcolor'],
                )
            );

		    add_settings_field(
                'login_button_color',
                __( 'Login Button Text', 'wp_suitedash_login' ),
                array( &$this, 'style_color_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_button_color',
                    'class' => 'suitedash_settings_row',
                    'default_value' => $this->default_value['login_button_color'],
                )
            );

            add_settings_field(
                'login_form_effect',
                __( 'Login Form Effect', 'wp_suitedash_login' ),
                array( &$this, 'login_form_effect_field' ),
                'suitedash_login',
                'sd_style_section',
                array(
                    'label_for' => 'login_form_effect',
                    'class' => 'suitedash_settings_row',
                )
            );

        }


        /**
         * Default section callback function
         *
         * @param $args
         */
        function default_section_cb( $args ) {

        }


        /**
         * to ssl
         *
         * @param $url string
         *
         * @return string
         */
        function to_ssl( $url ) {
            if ( false === strpos( $url, 'https://' ) ) {
                $url = 'https://' . ltrim( $url, 'http://' );
            }

            return $url;
        }


        /**
         * Input for form request settings
         *
         * @param $args
         */
        function form_request_field( $args ) {
            $options = get_option( 'suitedash_login' ); ?>
            <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                   name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]"
                   value="<?php echo ! empty( $options[$args['label_for']] ) ? esc_attr( $this->to_ssl( $options[$args['label_for']] )) : '' ?>"
                   class="regular-text" />
            <p class="description" id="tagline-description">If this field is left empty, the login form will direct through the default URL <strong>"https://app.suitedash.com/site/login"</strong></p>
		    <p id="tagline-description" class="description">To setup your Custom Domain URL, navigate to this URL <strong><a href="https://app.suitedash.com/domainSettings">https://app.suitedash.com/domainSettings</a></strong></p>

            <?php
        }

        /**
         * Input for form request settings
         *
         * @param $args
         */
        function form_logo_field( $args ) {
            $options = get_option( 'suitedash_login' );

            $options['logo_id']  = !empty($options['logo_id']) ? $options['logo_id']  : '';
            $src = !empty($options['logo_id']) ? wp_get_attachment_image_url($options['logo_id']) : '';

            echo '<img width="150" id="preview-logo" src="' . $src . '" style="clear: both; display: block; margin-bottom: 10px;" />';
            ?>
                <input type="hidden" name="suitedash_login[logo_id]" id="logo_id" value="<?php echo esc_attr( $options['logo_id'] ); ?>" class="regular-text" />
                <input type='button' class="button-primary" value="<?php esc_attr_e( 'Select a image', 'wp_suitedash_login' ); ?>" id="media_manager"/>
                <p class="description" id="tagline-description">The image will be displayed in original size</p>

            <?php
        }

        /**
         * Input for form request status
         *
         * @param $args
         */
        function form_request_status( $args ) {
            ?>

            <span id="sdl_check_status"><div class="sdl_login_box_loading"><div></div><div></div><div></div><div></div></div></span> <span id="sdl_check_status_error"></span>

            <style type="text/css">

                .sdl_ok {
                    color: #00aa00;
                    font-weight: bold;
                }

                .sdl_error {
                    color: #ee2200;
                    font-weight: bold;
                }

                .sdl_login_box_loading {
                    display: inline-block;
                    position: relative;
                    width: 25px;
                    height: 25px;
                }
                .sdl_login_box_loading div {
                    position: absolute;
                    top: 13px;
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                    background: #41CEFF;
                    animation-timing-function: cubic-bezier(0, 1, 1, 0);
                }
                .sdl_login_box_loading div:nth-child(1) {
                    left: 8px;
                    animation: sdl_login_box_loading1 0.6s infinite;
                }
                .sdl_login_box_loading div:nth-child(2) {
                    left: 8px;
                    animation: sdl_login_box_loading2 0.6s infinite;
                }
                .sdl_login_box_loading div:nth-child(3) {
                    left: 32px;
                    animation: sdl_login_box_loading2 0.6s infinite;
                }
                .sdl_login_box_loading div:nth-child(4) {
                    left: 56px;
                    animation: sdl_login_box_loading3 0.6s infinite;
                }
                @keyframes sdl_login_box_loading1 {
                    0% {
                        transform: scale(0);
                    }
                    100% {
                        transform: scale(1);
                    }
                }
                @keyframes sdl_login_box_loading3 {
                    0% {
                        transform: scale(1);
                    }
                    100% {
                        transform: scale(0);
                    }
                }
                @keyframes sdl_login_box_loading2 {
                    0% {
                        transform: translate(0, 0);
                    }
                    100% {
                        transform: translate(24px, 0);
                    }
                }



            </style>

            <script type="text/javascript">

              document.addEventListener("DOMContentLoaded", function () {

                  var sdl_check_status = jQuery('#sdl_check_status');
                  sdl_check_status.removeClass( 'sdl_ok sdl_error' );


                  jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    data: 'action=suitedash_request_check_status&nonce=<?php echo wp_create_nonce( 'SUITEDASH_REQUEST_CHECK_STATUS' . get_current_user_id() )?>',
                    dataType: "json",
                    success: function( data ) {
                      if ( data.status ) {
                        sdl_check_status.text( 'OK' );
                        sdl_check_status.addClass( 'sdl_ok' );
                      } else {
                        sdl_check_status.text( 'FAILED' );
                        sdl_check_status.addClass( 'sdl_error' );
                        jQuery( '#sdl_check_status_error' ).text( '(' + data.message + ')' );
                      }
                    },
                    error: function (data) {
                      sdl_check_status.text( 'FAILED' );
                      sdl_check_status.addClass( 'sdl_error' );
                      jQuery( '#sdl_check_status_error' ).text( '(Error W002P: Something wrong with the AJAX request.)' );
                    }
                  });

              });

            </script>


            <?php
        }


        /**
         * Input for form text settings
         *
         * @param $args
         */
        function form_text_field( $args ) {
            $options = get_option( 'suitedash_login' );
            $default_value = !empty( $args['default_value'] ) ? $args['default_value'] : '';
            ?>
            <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                   name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]"
                   value="<?php echo isset( $options[$args['label_for']] ) ? esc_attr( $options[$args['label_for']] ) : $default_value ?>"
                   class="regular-text" />
            <?php
        }

        /**
         * Input for form text settings
         *
         * @param $args
         */
        function form_switch_field( $args ) {
            $options = get_option( 'suitedash_login' );
            $checked = isset( $options[$args['label_for']] ) ? checked( $options[$args['label_for']], 'yes', false ) : false;
            ?>

            <style type="text/css">

                .sdl_settings_switch {
                    position: relative;
                    display: inline-block;
                    vertical-align: initial;
                    /*width: 50px;*/
                    width: 40px;
                    /*height: 30px;*/
                    height: 25px;
                    margin-right:8px;
                }

                .sdl_settings_switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .sdl_settings_switch_slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    -webkit-transition: .4s;
                    transition: .4s;
                }

                .sdl_settings_switch_slider:before {
                    position: absolute;
                    content: "";
                    /*height: 20px;*/
                    height: 17px;
                    /*width: 20px;*/
                    width: 17px;
                    /*left: 6px;*/
                    left: 4px;
                    /*bottom: 5px;*/
                    bottom: 4px;
                    background-color: white;
                    -webkit-transition: .4s;
                    transition: .4s;
                }

                input:checked + .sdl_settings_switch_slider {
                    background-color: #2485b5;
                }

                input:focus + .sdl_settings_switch_slider {
                    box-shadow: 0 0 1px #2485b5;
                }

                input:checked + .sdl_settings_switch_slider:before {
                    /*-webkit-transform: translateX(20px);*/
                    /*-ms-transform: translateX(20px);*/
                    /*transform: translateX(20px);    */

                    -webkit-transform: translateX(15px);
                    -ms-transform: translateX(15px);
                    transform: translateX(15px);
                }

                /* Rounded sliders */
                .sdl_settings_switch_slider.sdl_settings_switch_slider_round {
                    border-radius: 30px;
                }

                .sdl_settings_switch_slider.sdl_settings_switch_slider_round:before {
                    border-radius: 50%;
                }

            </style>


            <label class="sdl_settings_switch">

                <input type="hidden" id="sdl_settings_<?php echo esc_attr( $args['label_for'] ); ?>_hidden" name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]" value="no" />

                <input type="checkbox" <?php echo $checked ?>
                       id="sdl_settings_<?php echo esc_attr( $args['label_for'] ); ?>"
                       name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]"
                       value="yes"
                       class="" data-field_id="<?php echo esc_attr( $args['label_for'] ); ?>"
                />

                <span class="sdl_settings_switch_slider sdl_settings_switch_slider_round"></span>

            </label>


            <?php
        }


        /**
         * Input for colorpicker fields
         *
         * @param $args
         */
        function style_color_field( $args ) {
            $options = get_option( 'suitedash_login' );
            $default_value = !empty( $args['default_value'] ) ? $args['default_value'] : '';
            ?>

            <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                   name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]"
                   value="<?php echo ! empty( $options[$args['label_for']] ) ? esc_attr( $options[$args['label_for']] ) : '' ?>"
                   data-default-color="<?php echo $default_value ?>"
                   class="regular-text sd_colorfield" />
            <?php
        }


        /**
         * Selectbox for effects settings
         *
         * @param $args
         */
        function login_form_effect_field( $args ) {
            $options = get_option( 'suitedash_login' ); ?>
            <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                    name="suitedash_login[<?php echo esc_attr( $args['label_for'] ); ?>]">
                <option value="" <?php selected( empty( $options[$args['label_for']] ) ) ?>><?php _e( '(None)', 'wp_suitedash_login' ) ?></option>
                <option value="fade" <?php selected( ! empty( $options[$args['label_for']] ) && $options[$args['label_for']] == 'fade' ) ?>><?php _e( 'Fade', 'wp_suitedash_login' ) ?></option>
                <option value="bounce" <?php selected( ! empty( $options[$args['label_for']] ) && $options[$args['label_for']] == 'bounce' ) ?>><?php _e( 'Bounce', 'wp_suitedash_login' ) ?></option>
                <option value="zoom" <?php selected( ! empty( $options[$args['label_for']] ) && $options[$args['label_for']] == 'zoom' ) ?>><?php _e( 'Zoom', 'wp_suitedash_login' ) ?></option>
            </select>
            <?php
        }


        /**
         * Add options page to backend admin menu
         */
        function add_options_page() {
            add_options_page(
                __( 'SuiteDash Login Options', 'wp_suitedash_login' ),
                __( 'SuiteDash Login', 'wp_suitedash_login' ),
                'manage_options',
                'suitedash_login',
                array( $this, 'options_page_cb' )
            );
        }


        /**
         * Options page callback function
         */
        function options_page_cb() {
            // check user capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            } ?>

            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <form action="options.php" method="post">
                    <?php settings_fields( 'suitedash_login' );
                    do_settings_sections( 'suitedash_login' );
                    submit_button( __( 'Save Settings', 'wp_suitedash_login' ) ); ?>
                </form>
            </div>
            <?php
        }


        /**
         * Login form Shortcode
         */
        function suitedash_login() {
            $this->load_popup = true;

            $options = get_option( 'suitedash_login' );
            ob_start(); ?>

            <style>
                .sdl_load_form_button {
                    cursor: pointer;
                    /*background: #44c4e7;*/
                    width: 100px;
                    padding: 10px 15px;
                    border: 0;
                    /*color: #fff;*/
                    color: <?php echo ! empty( $options['login_button_color'] ) ? $options['login_button_color']  . ' !important' : $this->default_value['login_button_color'] ?>;
                    background-color: <?php echo ! empty( $options['login_button_bgcolor'] ) ? $options['login_button_bgcolor']  . ' !important' : $this->default_value['login_button_bgcolor'] ?>;
                    font-size: 14px;
                    font-weight: 400;
                }
            </style>

            <input type="button" class="button sdl_load_form_button sdl_load_form" value="<?php _e( 'Login', 'wp_suitedash_login' ) ?>" />

            <?php return ob_get_clean();
        }

        /**
         * Login form Shortcode without popup
         */
        function suitedash_login_form() {
            $this->load_popup = true;

            ob_start(); ?>

            <div id="sdl_login_form_show">
                <?php
                    $this->render_login_form();
                ?>
            </div>

            <?php
            return ob_get_clean();
        }

        /**
         * Login form Shortcode without popup
         */
        function render_login_form() {
            $this->load_popup = true;

            $options = get_option( 'suitedash_login' );
            $options['logo_id']  = !empty($options['logo_id']) ? $options['logo_id']  : '';
            $logo_src = !empty($options['logo_id']) ? wp_get_attachment_image_url($options['logo_id'], 'full') : '';

            $request_url = !empty( $options['form_request'] ) ? $this->to_ssl( rtrim( $options['form_request'], '/' ) ) : 'https://app.suitedash.com';

            $registration_url = !empty( $options['registration_url'] ) ? $this->to_ssl( $options['registration_url'] ) : '';

            ob_start(); ?>

            <section class="sdl_login_box animated flipInX">

                <?php
                $form_title = isset( $options['form_title'] ) ? $options['form_title'] : $this->default_value['form_title'];
                if ( !empty( $logo_src ) ) {
                    echo '<div class="sdl-logo-wrapper"><img class="sdl-logo" src="' . $logo_src . '" /></div>';
                }

                if ( !empty( $form_title ) ) {
                    echo '<span class="sdl_login_title">' . $form_title . '</span>';
                }

                ?>


                <form method="post" action="<?php echo $request_url ?>/site/login" class="sdl_login_form" autocomplete="off">
                    <p class="sdl_login_error"></p>
                    <input placeholder="<?php _e( 'Email', 'wp_suitedash_login' ) ?>" type="text" name="LoginForm[email]" class="sdl_login_email"></input>
                    <input placeholder="<?php _e( 'Password', 'wp_suitedash_login' ) ?>" type="password" name="LoginForm[password]" class="sdl_login_password"></input>

                    <label for="rememberme">
                        <input type="checkbox" id="rememberme" name="LoginForm[rememberMe]" value="1" tabindex="30">
                        <?php _e( 'Remember me', 'wp_suitedash_login' ) ?>
                    </label>

                    <div class="sdl_login_button_box">
                        <button class="sdl_login_submit"><?php _e( 'Login', 'wp_suitedash_login' ) ?></button>
                    </div>

                    <p class="sdl_bottom_links sdl_forgot_password">
                        <a href="<?php echo $request_url ?>/site/havingTrouble" target="_blank">
                            <?php _e( 'Forgot your password?', 'wp_suitedash_login' ) ?>
                        </a>
                    </p>

                    <?php if ( ! empty( $registration_url ) ) { ?>

                        <p class="sdl_bottom_links sdl_registration_link">
                            <a href="<?php echo $registration_url ?>" <?php echo ( ! empty( $options['registration_open_new_tab'] ) && 'yes' == $options['registration_open_new_tab'] ) ? 'target="_blank"' : '' ?> >
                                <?php echo ( ! empty( $options['registration_link_text'] ) ) ? $options['registration_link_text'] : $this->default_value['registration_link_text'] ?>
                            </a>
                        </p>

                    <?php } ?>

                </form>
            </section>

            <?php
            echo ob_get_clean();
        }

        /**
         * Trigger displaying login form popup
         * @param $items
         * @param $args
         * @return mixed
         */
        function nav_menu_objects( $items, $args ) {
            foreach ( $items as $item ) {
                if ( ! empty( $item->classes ) && in_array( 'sdl_load_form', $item->classes ) ) {
                    $this->load_popup = true;

                    $options = get_option( 'suitedash_login' );
                    if ( ! empty( $options['login_form_effect'] ) )
                        $this->user_print_scripts();
                }
            }

            return $items;
        }


        /**
         * Displaying popup HTML+JS+CSS
         *
         * @return string
         */
        function footer_loading() {
            if ( ! $this->load_popup )
                return '';

            $options = get_option( 'suitedash_login' );

            if ( ! empty( $options['login_form_effect'] ) )
                $this->user_print_scripts();

            $effect = ! empty( $options['login_form_effect'] ) ? "'" . $options['login_form_effect'] . "'" : '';
            $effect = ( ! empty( $effect ) && $options['login_form_effect'] != 'zoom' ) ? $effect . ", 1000" : $effect;
            ob_start(); ?>

            <script type="text/javascript">

                document.addEventListener("DOMContentLoaded", function () {

                    jQuery('body').on('click', '.sdl_load_form', function(e) {
                        if ( jQuery('#sdl_login_popup').hasClass('sdl_visible') )
                            return false;

                        jQuery('#sdl_login_popup').addClass('sdl_visible').show( <?php echo $effect ?> );
                        e.stopPropagation();

                        jQuery(document).mouseup(function (event) {
                            var container = jQuery('#sdl_login_popup');
                            if (container.has(event.target).length === 0){
                                container.removeClass('sdl_visible').hide( <?php echo $effect ?> );
                                jQuery('body').unbind( event );
                            }
                        });

                    });


                    jQuery( 'body' ).on( 'click', '.sdl_login_submit', function(e) {
                        e.preventDefault();
                        var obj = jQuery(this);
                        var form = jQuery(this).parents('form');
                        var error_box = form.find( '.sdl_login_error' );
                        var email = form.find( '.sdl_login_email' ).val().replace( '+', '<><><>');
                        var password = form.find( '.sdl_login_password' ).val().replace( '+', '<><><>');
                        password = password.replace( '&', '<>>><<<>');

                        if ( email == '' && password == '' ) {
                            return false;
                        }

                        error_box.hide();

                        obj.addClass( 'sdl_login_box_loading' );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php') ?>',
                            data: 'action=suitedash_request&email=' + email + '&password=' + password,
                            dataType: "json",
                            success: function( data ) {

                                obj.removeClass( 'sdl_login_box_loading' );

                                if ( data.status && data.access_token_name && data.access_token ) {
                                    form.append("<input type='hidden' name='" + data.access_token_name + "' value='" + data.access_token + "' />");
                                    form.submit();
                                } else {
                                    error_box.html( data.message );
                                    error_box.show();
                                }
                            },
                            error: function (data) {

                                obj.removeClass( 'sdl_login_box_loading' );

                                error_box.html( '<?php _e( 'Something went wrong', 'wp_suitedash_login' ) ?>' );
                                error_box.show();
                            }
                        });
                    });

                });


            </script>

            <style>


                #sdl_login_popup {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 99999999999999;
                    display: none;
                    box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
                }


                .sdl_login_box {
                    /*background: #fff;*/
                    background-color: <?php echo ! empty( $options['login_form_background'] ) ? $options['login_form_background'] . ' !important' : $this->default_value['login_form_background'] ?>;
                    width: 350px;
                    padding: 40px 30px; 20px 30px;
                }

                .sdl-logo-wrapper {
                    width: 100%;
                    text-align: center;
                }

                .sdl-logo {
                    border: none;
                }

                .sdl_login_box .sdl_login_title {
                    margin: 0 0 25px 0;
                    line-height: 1;
                    /*color: #44c4e7;*/
                    color: <?php echo ! empty( $options['login_form_title_color'] ) ? $options['login_form_title_color']  . ' !important;' : $this->default_value['login_form_title_color'] ?>;
                    font-size: 18px;
                    font-weight: 400;
                    text-align: center;
                    display: block;
                }

                .sdl_login_box input[type="text"],
                .sdl_login_box input[type="password"] {
                    outline: none;
                    display: block;
                    width: 100%;
                    margin: 0 0 20px;
                    padding: 10px 15px;
                    border: 1px solid #ccc;
                    color: #ccc;
                    /*font-size: 14px;*/
                    /*font-wieght: 400;*/
                    box-sizing: border-box;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    transition: 0.2s linear;
                }

                .sdl_login_box label,
                .sdl_bottom_links {
                    outline: none;
                    display: block;
                    width: 100%;
                    margin: 0;
                    padding: 0 0 10px 0;
                    /*color: #666;*/
                    /*font-size: 14px;*/
                    /*font-wieght: 400;*/
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    transition: 0.2s linear;
                }

                .sdl_bottom_links {
                    text-align: center;
                    padding: 10px 0 0;
                }


                .sdl_login_box input:focus {
                    color: #333;
                    border: 1px solid #44c4e7;
                }

                .sdl_login_button_box {
                    width: 100%;
                    text-align: center;
                }

                .sdl_login_box button {
                    cursor: pointer;
                    /*background: #44c4e7;*/
                    width: 100%;
                    padding: 10px 15px;
                    border: 0;
                    /*color: #fff;*/
                    color: <?php echo ! empty( $options['login_button_color'] ) ? $options['login_button_color']  . ' !important' : $this->default_value['login_button_color'] ?>;
                    background-color: <?php echo ! empty( $options['login_button_bgcolor'] ) ? $options['login_button_bgcolor']  . ' !important' : $this->default_value['login_button_bgcolor'] ?>;
                    /*font-size: 14px;*/
                    /*font-weight: 400;*/
                }

                .sdl_login_box button:hover {
                    background-color: #369cb8;
                }

                .sdl_login_error {
                    display: none;
                    /*color: #666;*/
                    padding: 5px 10px;
                    margin: 0px 0 5px 0;
                    border-left: solid 2px red;
                    /*font-size: 14px;*/
                    /*font-weight: 400;*/
                }


                /*Texts Famaly*/
                .sdl_login_box input[type="text"],
                .sdl_login_box input[type="password"],
                .sdl_login_box label,
                .sdl_bottom_links,
                .sdl_login_box button,
                .sdl_login_error {
                    font-size: 14px;
                    font-weight: 400;
                }

                  /*Texts Color*/
                .sdl_login_box label,
                .sdl_bottom_links a,
                .sdl_login_error {
                    color: <?php echo ! empty( $options['login_form_color'] ) ? $options['login_form_color']  . ' !important;' : $this->default_value['login_form_color'] ?>;
                }



                .sdl_login_box_loading {
                    -webkit-animation: sdl_login_pulse 1s linear infinite alternate;
                    -moz-animation:    sdl_login_pulse 1s linear infinite alternate;
                    -o-animation:      sdl_login_pulse 1s linear infinite alternate;
                    animation:         sdl_login_pulse 1s linear infinite alternate;
                }

                @-webkit-keyframes sdl_login_pulse {
                    0%   { opacity: 1; width: 100%; }
                    100% { opacity: 0.2; width: 80%; }
                }
                @-moz-keyframes sdl_login_pulse {
                    0%   { opacity: 1; width: 100%; }
                    100% { opacity: 0.2; width: 80%; }
                }
                @-o-keyframes sdl_login_pulse {
                    0%   { opacity: 1; width: 100%; }
                    100% { opacity: 0.2; width: 80%; }
                }
                @keyframes sdl_login_pulse {
                    0%   { opacity: 1; width: 100%; }
                    100% { opacity: 0.2; width: 80%; }
                }


            </style>

            <div id="sdl_login_popup">

                <?php $this->render_login_form(); ?>

            </div>
            <?php echo ob_get_clean();
            return '';
        }


        /**
         * Suitedash request
         */
        function suitedash_request() {
            $postfields = array(
                'email'     => !empty( $_POST['email'] ) ? trim( str_replace( '<><><>', '+', $_POST['email'] ) ) : '',
                'password'  => !empty( $_POST['password'] ) ? trim( str_replace( array('<><><>', '<>>><<<>' ), array( '+', '&' ), $_POST['password'] ) ) : '',
                'webLogin'  => true
            );

            $postfields['password'] = trim( $postfields['password'] );

            $options = get_option( 'suitedash_login' );
            $request_url = !empty( $options['form_request'] ) ? $this->to_ssl( rtrim( $options['form_request'], '/' ) ) : 'https://app.suitedash.com';

            $response = wp_remote_post(
                $request_url . "/api/login",
                array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => $postfields,
                    'cookies' => array()
                )
            );

            if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
                if (!empty($_POST['devtest'])) {
                    var_dump($response);
                    exit;
                }

                if ( is_wp_error( $response ) ) {
                    $message = $response->get_error_message();
                    wp_die( json_encode( array( 'status' => false, 'message' => $message ) ) );
                }

                wp_die( json_encode( array( 'status' => false, 'message' => __( 'Error W003P: Something wrong with the request.', 'wp_suitedash_login' ) ) ) );
            }

            $answer = json_decode( $response['body'], true );

            if ( $answer['success'] && !empty( $answer['accessToken'] ) ) {
                $accessToken = explode(':', $answer['accessToken']);

                wp_die( json_encode( array( 'status' => true, 'access_token_name' => $accessToken[0], 'access_token' => $accessToken[1] ) ) );
            } else {
                wp_die( json_encode( array( 'status' => false, 'message' => $answer['message'] ) ) );
            }
        }

        /**
         * Suitedash check status of request
         */
        function ajax_suitedash_request_check_status() {

            if ( empty( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'SUITEDASH_REQUEST_CHECK_STATUS' . get_current_user_id() ) ) {
                return ;
            }

            $options = get_option( 'suitedash_login' );
            $request_url = !empty( $options['form_request'] ) ? $this->to_ssl( rtrim( $options['form_request'], '/' ) ) : 'https://app.suitedash.com';

            $response = wp_remote_post(
                $request_url . "/api/login",
                array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(),
                    'cookies' => array()
                )
            );

            if ( is_wp_error( $response ) ) {
                $message = $response->get_error_message();
                wp_die( json_encode( array( 'status' => false, 'message' => $message ) ) );
            }

            wp_die( json_encode( array( 'status' => true ) ) );
        }


        /**
         * Extend nav-menu functionality for custom menu item type
         */
        function nav_menus() {
            wp_register_script( 'nav-menu-custom-js', WP_PLUGIN_URL . '/client-portal-suitedash-login/js/nav-menu-custom.js', false, '1.0.0' );
            wp_enqueue_script( 'nav-menu-custom-js', false, array('nav-menu'), '1.0.0', true );
        }


        /**
         * Extend nav-menu functionality for custom menu item type
         */
        function manage_nav_menus() {
            add_meta_box( 'wp_suitedash_login', __( 'SuiteDash Login', 'wp_suitedash_login' ), array( &$this, 'manage_nav_menus_cb' ), 'nav-menus', 'side', 'default' );
        }


        /**
         * Extend nav-menu functionality for custom menu item type Callback function
         */
        function manage_nav_menus_cb() {
            global $_nav_menu_placeholder, $nav_menu_selected_id;

            $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1; ?>

            <div class="wp_suitedash_logindiv" id="wp_suitedash_logindiv">
                <input type="hidden" value="custom" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" />

                <p id="menu-item-name-wrap" class="wp-clearfix">
                    <label class="howto" for="custom-menu-item-name"><?php _e( 'Link Text' ); ?></label>
                    <input id="wp_suitedash_login-item-name" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" type="text" class="regular-text menu-item-textbox" />
                </p>

                <p class="button-controls wp-clearfix">
                    <span class="add-to-menu">
                        <input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" id="submit-wp_suitedash_logindiv" />
                        <span class="spinner"></span>
                    </span>
                </p>

            </div><!-- /.wp_suitedash_logindiv -->
            <?php
        }

    }

    $suitedash_login = WP_Suitedash_Login::getInstance();
}