<?php

class giftup_curl_test_response {
    public $can_access_api = false;
    public $can_access_example_com = false;
    public $tls_1_2_enabled = false;
    public $tls_1_2_response_body = "";
    public $code = 0;
    public $body = "";
    public $renderable_body = "";
    public $api_key = "";

    function __construct( $can_access_api, $can_access_example_com, $tls_1_2_enabled, $tls_1_2_response_body, $api_response, $api_key ) {
        $this->code = $api_response->code;
        $this->body = $api_response->body;
        $this->renderable_body = $api_response->renderable_body;
        $this->api_key = $api_key;

        $this->can_access_api = $can_access_api;
        $this->can_access_example_com = $can_access_example_com;
        $this->tls_1_2_enabled = $tls_1_2_enabled;
        $this->tls_1_2_response_body = $tls_1_2_response_body;
    }
}

class giftup_diagnostics {
    // Diagnostics output
    public static $diagnostics = "";

    public static function new_group() {
        self::$diagnostics .= "<HR size=1 color=red style='margin: 2rem 0; padding: 0'>";
    }

    public static function append( $debug_msg ) {
        self::$diagnostics .= "<div>" . $debug_msg . "</div>";
    }

    public static function render(){
        $debug = giftup_options::get_woocommerce_diagnostics_mode();
        
        global $wp_version;
    
        if ( !wp_doing_ajax() && $debug ) {
            echo "<div style='border: 5px solid red; color: red; padding: 2rem; background-color: rgba(255, 0, 0, 0.2);'>";
            echo " <h6 style='margin: 0 0 1rem 0; padding: 0;'>Gift Up! diagnostics</h6>";
            echo "Gift Up! plugin version: " . GIFTUP_VERSION . "<BR>";
            echo "WordPress version: " . $wp_version . "<BR>";
            echo "PHP version: " . phpversion() . "<BR>";

            $wc_version = self::woocommerce_installed_version();
            if ($wc_version !== null) {
                echo "WooCommerce version: " . $wc_version . "<BR>";

                if (giftup_options::get_woocommerce_enabled() == true) {
                    echo "WooCommerce integration enabled<br>";
                    echo "Apply to shipping: " . (giftup_options::get_woocommerce_apply_to_shipping() ? "true" : "false") . "<br>";
                    echo "Apply to taxes: " . (giftup_options::get_woocommerce_apply_to_taxes() ? "true" : "false") . "<br>";
                }
            }

            echo self::$diagnostics;
            echo "</div>";
        }
    }

    public static function woocommerce_installed_version() {
        $version = null;

        try {
            if ( defined( 'WC_VERSION' ) ) {
                $version = WC_VERSION;
            } else if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
                $version = WOOCOMMERCE_VERSION;
            } else {
                $version = self::get_woo_version_number();
            }

            return $version;
        } catch (exception $e) {
            return null;
        }

        return null;
    }

    public static function is_woocommerce_activated() {
        try {
            if ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
                return is_plugin_active( 'woocommerce/woocommerce.php' );
            }
        } catch (exception $e) {
            return true; // assumed active
        }

        return true; // assumed active
    }

    private static function get_woo_version_number() {
        try {
            if ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
                $plugin_data = get_file_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', array( 'Version' => 'Version' ) );

                if ( is_array($plugin_data) && isset($plugin_data['Version']) ) {
                    return $plugin_data['Version'];
                }
    
                if ( ! function_exists( 'get_plugins' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                
                $plugin_folder = get_plugins( '/' . 'woocommerce' );
                $plugin_file = 'woocommerce.php';
                
                if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
                    return $plugin_folder[$plugin_file]['Version'];
                } else {
                    return null;
                }
            }
        } catch (exception $e) {
            return null;
        }

        return null;
    }

    public static function get_plugins_list() {
        try {
            $format = "<div>{{Title}} by {{Author}} (version: {{Version}})</div>";
            $cache = 5;
            $by_author = 'false';
        
            $plugins = self::get_plugin_list_data( $cache );
        
            // Extract each plugin and format the output.
            $output = '';
            foreach ( $plugins as $plugin_file => $plugin_data ) {
                if ( is_plugin_active( $plugin_file ) ) {
                    $output .= self::format_plugin_list( $plugin_data, $format );
                }
            }
        
            return $output;
        } catch (exception $e) {
            return $e->getMessage();
        }
    }

    private static function get_plugin_list_data( $cache ) {
        // Attempt to get plugin list from cache.
        if ( ! $cache ) {
            $cache = 'no';
        }
    
        $plugins   = false;
        $cache_key = 'plugins_list';

        if ( is_numeric( $cache ) ) {
            $plugins = get_transient( $cache_key );
        }
    
        // If not using cache, generate a new list and cache that.
    
        if ( ! $plugins ) {
            $plugins = get_plugins();

            if ( ( '' !== $plugins ) && ( is_numeric( $cache ) ) ) {
                set_transient( $cache_key, $plugins, MINUTE_IN_SECONDS * $cache );
            }
        }
    
        return $plugins;
    }

    private static function format_plugin_list( $plugin_data, $format ) {

        // Allowed tag.
        $plugins_allowedtags = array(
            'a'       => array(
                'href'  => array(),
                'title' => array(),
            ),
            'abbr'    => array( 'title' => array() ),
            'acronym' => array( 'title' => array() ),
            'code'    => array(),
            'em'      => array(),
            'strong'  => array(),
        );
    
        // Sanitize all displayed data.
        $plugin_data['Title']     = wp_kses( $plugin_data['Title'], $plugins_allowedtags );
        $plugin_data['PluginURI'] = wp_kses( $plugin_data['PluginURI'], $plugins_allowedtags );
        $plugin_data['AuthorURI'] = wp_kses( $plugin_data['AuthorURI'], $plugins_allowedtags );
        $plugin_data['Version']   = wp_kses( $plugin_data['Version'], $plugins_allowedtags );
        $plugin_data['Author']    = wp_kses( $plugin_data['Author'], $plugins_allowedtags );
    
        // Replace the tags.
        $format = self::replace_plugin_list_tags( $plugin_data, $format );
    
        return $format;
    }

    private static function replace_plugin_list_tags( $plugin_data, $format ) {
        $format = strtr(
            $format,
            array(
                '{{Title}}'        => $plugin_data['Title'],
                '{{PluginURI}}'    => $plugin_data['PluginURI'],
                '{{AuthorURI}}'    => $plugin_data['AuthorURI'],
                '{{Version}}'      => $plugin_data['Version'],
                '{{Description}}'  => $plugin_data['Description'],
                '{{Author}}'       => $plugin_data['Author'],
                '{{LinkedTitle}}'  => "<a href='" . $plugin_data['PluginURI'] . "'>" . $plugin_data['Title'] . '</a>',
                '#Title#'          => $plugin_data['Title'],
                '#PluginURI#'      => $plugin_data['PluginURI'],
                '#AuthorURI#'      => $plugin_data['AuthorURI'],
                '#Version#'        => $plugin_data['Version'],
                '#Description#'    => $plugin_data['Description'],
                '#Author#'         => $plugin_data['Author'],
                '#LinkedTitle#'    => "<a href='" . $plugin_data['PluginURI'] . "'>'" . $plugin_data['Title'] . '</a>',
                '{{'               => '<',
                '}}'               => '>',
                '{'                => '<',
                '}'                => '>',
            )
        );
    
        return $format;
    }

    public static function test_curl( $api_key ) {
        // Let's test the authenticated Gift Up! Ping endpoint
        $api_response = giftup_api::invoke( '/ping', 'GET', null, $api_key );

        if ( $api_response->success ) {
            return new giftup_curl_test_response( true, true, true, '', $api_response, $api_key );
        }

        $args = array(
            'timeout' => 15,
            'headers' => array(
                'accept' => '*/*',
                'user-agent' => 'WordPress/GiftUp-WordPress-Plugin'
            )
        );

        // Now let's do a TLS check via https://www.howsmyssl.com/a/check
        $tls_1_2_enabled = false;
        $tls_1_2_response = wp_remote_get( "https://www.howsmyssl.com/a/check", $args );

        $tls_1_2_response_body = wp_remote_retrieve_body( $tls_1_2_response );
        $tls_1_2_response_code = wp_remote_retrieve_response_code( $tls_1_2_response );
        $tls_1_2_response_success = $tls_1_2_response_code >= 200 && $tls_1_2_response_code < 300;

        if ( $tls_1_2_response_success ) {
            $tls_result = json_decode( $tls_1_2_response_body, true );

            if ( $tls_result['tls_version'] == 'TLS 1.12' || $tls_result['tls_version'] == 'TLS 1.13' ) {
                $tls_1_2_enabled = true;
            } else {
                preg_match_all('![\d,\.]+!', $tls_result['tls_version'], $matches);
                if ( is_array( $matches ) ) {
                    $tls_1_2_enabled = floatval( $matches[0][0] ) >= 1.2;
                }
            }
        }

        // Let's check access to https://www.example.com
        $example_response = wp_remote_get( "https://www.example.com", $args );
        $example_response_code = wp_remote_retrieve_response_code( $example_response );
        $example_response_success = $example_response_code >= 200 && $example_response_code < 300;

        // Let's also test the anonymous Gift Up! Ping endpoint
        $ping_response = giftup_api::invoke( '/ping', 'HEAD' );

        $result = new giftup_curl_test_response( $ping_response->success, $example_response_success, $tls_1_2_enabled, $tls_1_2_response_body, $api_response, $api_key );

        if ( $result->can_access_api ) {
            $message = 'The API key you have entered does not work, please enter a valid Gift Up! API key.';
            $message = $message . '<br>--';
            $message = $message . '<br>Accessing: https://api.giftup.app/ping';
            $message = $message . '<br>With API key: "<span style="word-break: break-all">' . $result->api_key . '</span>"';
            $message = $message . '<br>Response code: ' . $result->code;

            if ( $result->renderable_body ) {
                $message = $message . '<br>Response body: ' . $result->renderable_body;
            }

            return array(
                'message' => $message,
                'status' => 'error'
            );
        }
        else if ( ! $result->tls_1_2_enabled ) {
            $message = 'We cannot access the Gift Up! API to validate your API key because it appears that your server is incapable of making outbound cURL requests using TLS 1.2. ';
            $message = $message . '<br>Please upgrading your PHP to version 5.5.19 or higher and cURL version 7.34.0 or higher/OpenSSL @ 1.0.1 or higher. ';
            $message = $message . '<br><br>There is a great plugin for testing your WordPress installation\'s capability here: <a href="https://wordpress.org/plugins/tls-1-2-compatibility-test/">https://wordpress.org/plugins/tls-1-2-compatibility-test/</a>';
            $message = $message . '<br>--';
            $message = $message . '<br>Accessing: https://api.giftup.app/';
            $message = $message . '<br>Response code: ' . $result->code;

            if ( $result->renderable_body ) {
                $message = $message . '<br>Response body: ' . $result->renderable_body;
            }

            $message = $message . self::add_diagnostics( $result );

            return array(
                'message' => $message,
                'status' => 'error'
            );
        }
        else if ( $result->can_access_example_com ) {
            $message = 'We cannot access the Gift Up! API at the moment to validate your API key, please try again in a few moments';
            $message = $message . '<br>--';
            $message = $message . '<br>Accessing: https://api.giftup.app/';
            $message = $message . '<br>Response code: ' . $result->code;

            if ( $result->renderable_body ) {
                $message = $message . '<br>Response body: ' . $result->renderable_body;
            }

            $message = $message . self::add_diagnostics( $result );

            return array(
                'message' => $message,
                'status' => 'error'
            );
        }
        else {
            $message = 'Your Wordpress instance is unable to make ourbound cUrl requests to any external web service/API, including the Gift Up! API at https://api.giftup.app. ';
            $message = $message . '<br>--';
            $message = $message . '<br>How to resolve:';
            $message = $message . '<br>Please review any WordPress security plugins and ensure they are configured to allow outbound cUrl requests and also review your webhost\'s security system/firewall settings to ensure that it is configured to allow your WordPress instance to send/receive on port 443.';
            $message = $message . '<br>--';
            $message = $message . '<br>Accessing: https://api.giftup.app/ping';
            $message = $message . '<br>Response code: ' . $result->code;

            if ( $result->renderable_body ) {
                $message = $message . '<br>Response body: ' . $result->renderable_body;
            }

            $message = $message . self::add_diagnostics( $result );

            return array(
                'message' => $message,
                'status' => 'error'
            );
        }
    }
}