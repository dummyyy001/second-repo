<?php

class giftup_api_response {
    public $success = false;
    public $code = 0;
    public $body = "";
    public $renderable_body = "";

    function __construct( $response ) {
        $body = wp_remote_retrieve_body( $response );

        $this->code = wp_remote_retrieve_response_code( $response );
        $this->success = $this->code >= 200 && $this->code < 300;
        $this->body = self::isJson( $body ) ? json_decode( $body, true ) : $body;
        $this->renderable_body = self::isJson( $body ) ? $body : '<div style="word-break: break-all; overflow-x: none; overflow-y: auto; max-height: 200px;">' . htmlentities( $body ) . '</div>';
    }

    function isJson( $string ) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

class giftup_api
{
    /**
    * Get a gift card remaining balance
    *
    * @return balance as decimal
    */
    public static function get_gift_card_balance( $code = null ) {
        if (empty($code)){
            $code = giftup_cache::get_gift_card_code();
        }

        if (!empty($code)) {
            $giftcard = self::get_gift_card($code);

            if ($giftcard !== NULL 
                && $giftcard['canBeRedeemed'] 
                && self::get_gift_card_is_valid($code)
                && isset($giftcard['remainingValue']) 
                && is_numeric($giftcard['remainingValue'])) {
                return $giftcard['remainingValue'];
            }
        }
        
        return 0;
    }

    /**
    * @return true if gift card has expired
    */
    public static function get_gift_card_is_valid( $code = null ) {
        if (empty($code)){
            $code = giftup_cache::get_gift_card_code();
        }

        if (!empty($code)) {
            $giftcard = self::get_gift_card($code);

            if ($giftcard !== NULL 
                && ($giftcard['hasExpired'] == 1 || $giftcard['notYetValid'] == 1))
            {
                return false;
            }
        }
        
        return true;
    }

    /**
    * @return true if gift card is currency backed
    */
    public static function get_is_currency_backed( $code = null ) {
        if (empty($code)){
            $code = giftup_cache::get_gift_card_code();
        }

        if (!empty($code)) {
            $giftcard = self::get_gift_card($code);

            if ($giftcard !== NULL
                && $giftcard['backingType'] !== NULL)
            {
                return strtolower( $giftcard['backingType'] ) == 'currency';
            }
        }
        
        return false;
    }
    
    /**
    * Get a gift card
    *
    * @return giftcard object
    */
    public static function get_gift_card( $code = null ) {
        // Look this up in our cache
        if ($code != null && giftup_cache::$giftcard != NULL && strtolower($code) == strtolower(giftup_cache::$giftcard['code']) ) {
            return giftup_cache::$giftcard;
        }

        if (empty($code)){
            $code = giftup_cache::get_gift_card_code();
        }

        if (!empty($code)) {
            $debug = giftup_options::get_woocommerce_diagnostics_mode();

            if ($debug) {
                giftup_diagnostics::append( "├ Looking up gift card " . $code . " via API" );
            }
        
            $response = self::invoke( '/gift-cards-woocommerce/' . rawurlencode( $code ) );
        
            if ($response->success) {
                giftup_cache::$giftcard = $response->body;
                return $response->body;
            }
        
            if ($debug) {
                giftup_diagnostics::append( "├ Gift card " . $code . " not found (" . $response->code . ")" );

                if ( $response->code != 404 && $response->renderable_body !== NULL && strlen( $response->renderable_body ) > 0 ) {
                    giftup_diagnostics::append( "├ " . $response->renderable_body );
                }
            }
        }
        
        return null;
    }
    
    /**
    * Get a list of gift cards
    *
    * @return list<giftcard> object
    */
    public static function get_gift_cards( $offset = 0, $limit = 10 ) {
        $response = self::invoke( '/gift-cards?offset=' . $offset . '&limit=' . $limit );
    
        if ($response->success) {
            return $response->body;
        }
        
        return null;
    }
    
    /**
    * Redeem a gift card
    *
    * @return boolean representing whether the redeem worked
    */
    public static function redeem_gift_card( $code, $value, $order_id ) {
        $giftcard = self::get_gift_card( $code );
    
        if ($giftcard == NULL) {
            return -1001;
        }
        
        $balance = self::get_gift_card_balance( $code );

        if ( $balance < $value ) {
            return -1002;
        }

        $rounded_value = $value;
        try {
            $rounded_value = round($value, 2, PHP_ROUND_HALF_DOWN);
        } catch(exception $e) {}

        if ($order_id === NULL || strlen($order_id) == 0) {
            $order_id = "(unknown)";
        }

        $reason = "Redeemed against WooCommerce order id " . $order_id;

        $payload = [];
        $response = self::invoke( '/gift-cards/' . rawurlencode( $code ) . '/redeem-woocommerce?amount=' . $rounded_value . '&reason=' . rawurlencode( $reason ), 'POST', $payload );
        
        if ( $response->success ) {
            return $response->body['redeemedAmount'];
        }

        //  Fallback GET request to avert 411 HTTP responses
        if ( $response->code == 411 ) {
            $cache_bust = self::generate_random_string(10);
            $response = self::invoke( '/gift-cards/' . rawurlencode( $code ) . '/redeem-woocommerce?amount=' . $rounded_value . '&reason=' . rawurlencode( $reason ) . '&cb=' . $cache_bust, 'GET' );
        }
        
        if ( $response->success ) {
            return $response->body['redeemedAmount'];
        }

        return -2000 - $response->code;
    }

    private static function generate_random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    /**
    * Add credit to a gift card
    *
    * @return boolean representing whether the add credit worked
    */
    public static function add_credit_to_gift_card( $code, $value, $order_id ) {
        $giftcard = self::get_gift_card( $code );
    
        if ($giftcard == NULL) {
            return false;
        }
        
        $balance = self::get_gift_card_balance( $code );

        if ( $balance < $value ) {
            return false;
        }

        $rounded_value = $value;
        try {
            $rounded_value = round($value, 2, PHP_ROUND_HALF_DOWN);
        } catch(exception $e) {}

        if ($order_id === NULL || strlen($order_id) == 0) {
            $order_id = "(unknown)";
        }

        $payload = [
            'amount' => $rounded_value,
            'reason' => "WooCommerce order cancelled " . $order_id
        ];

        $response = self::invoke( '/gift-cards/' . rawurlencode( $code ) . '/add-credit', 'POST', $payload );
        
        return $response->success;
    }
    
    /**
    * Get the company name
    *
    * @return  string
    */
    public static function get_company( $api_key = null ) {
        $response = self::invoke( '/company', 'GET', null, $api_key );
        
        if ( $response->success ) {
            return $response->body;
        }
        
        return null;
    }

    public static function get_woocommerce_connection_status()
    {
        $response = self::invoke( '/integrations/woocommerce/test?noprobe=true' );

        if ( $response->success ) {
            return $response->body;
        }

        return null;
    }

    public static function notify_connect_woocommerce()
    {
        $payload = [];
        $payload['storeUrl'] = get_site_url();

        $response = self::invoke( '/integrations/woocommerce/connect', 'POST', $payload );

        if ($response->success) {
            giftup_options::set_woocommerce_operating_mode( giftup_options::WOO_MODE_API );
        }

        return $response->success;
    }

    public static function notify_disconnect_woocommerce()
    {
        $response = self::invoke( '/integrations/woocommerce/disconnect', 'POST' );

        return $response->success;
    }

    public static function api_root()
    {
        if (isset( $_COOKIE['giftup_api_root'] )) {
            return $_COOKIE['giftup_api_root'];
        }

        return 'https://api.giftup.app';
    }

    public static function dashboard_root()
    {
        if (isset( $_COOKIE['giftup_dashboard_root'] )) {
            return $_COOKIE['giftup_dashboard_root'];
        }

        return 'https://giftup.app';
    }

    public static function different_roots_enabled()
    {
        if (!empty( $_COOKIE['giftup_dashboard_root'] ) or !empty( $_COOKIE['giftup_api_root'] )) {
            return true;
        }

        return false;
    }
    
    /**
    * Invoke an API call to Gift Up!
    *
    * @return  string
    */
    public static function invoke( $endpoint, $method = "GET", $data = null, $api_key = null ) {
        $root = self::api_root();
        $url = esc_url_raw( $root . $endpoint );
        $response = false;
        $json = null;
        
        if ($data !== NULL) {
            $json = json_encode( $data, JSON_FORCE_OBJECT );

            if ($json === NULL) {
                $json = "{ 'error': 'Could not serialize data into JSON' }";
            }
        }

        if ($api_key === NULL) {
            $api_key = giftup_options::get_api_key();
        }

        $plugin_version = GIFTUP_VERSION;
        $woocommerce_version = giftup_diagnostics::woocommerce_installed_version();
        $php_version = phpversion();
        global $wp_version;

        if ( $plugin_version === NULL || strlen( $plugin_version ) <= 0 ) {
            $plugin_version = "unknown";
        }
        if ( $php_version === NULL || strlen( $php_version ) <= 0 ) {
            $php_version = "unknown";
        }
        if ( $wp_version === NULL || strlen( $wp_version ) <= 0 ) {
            $wp_version = "unknown";
        }
        if ( $woocommerce_version === NULL || strlen( $woocommerce_version ) <= 0 ) {
            $woocommerce_version = "unknown";
        }

        $args = array(
            'timeout' => 60,
            'body' => $json,
            'headers' => array(
                'authorization' => 'Bearer ' . $api_key,
                'content-type' => 'application/json',
                'accept' => '*/*',
                'user-agent' => 'WordPress/GiftUp-WordPress-Plugin',
                'x-giftup-testmode' => giftup_options::get_woocommerce_is_in_test_mode() ? "true" : "false",
                'x-giftup-wordpress-plugin-version' => $plugin_version,
                'x-giftup-wordpress-php-version' => $php_version,
                'x-giftup-wordpress-version' => $wp_version,
                'x-giftup-woocommerce-version' => $woocommerce_version
            )
        );
        
        if ($method === "GET") {
            $response = wp_remote_get( $url, $args );
        }
        else if ($method === "POST") {
            $response = wp_remote_post( $url, $args );
        }
        else {
            $args = array(
                'method' => $method
            );
            $response = wp_remote_request( $url, $args );
        }
        
        if ( is_wp_error($response) ) {
            $error = $response->get_error_message();
            
            echo '<div id="message" class="notice notice-error">';
            echo '<p>';
            echo '<strong>';
            echo 'Error talking to Gift Up! at ' . $url . ' - ' . $error . '<br>';
            if (strpos($error, 'tls') !== false){
                echo '<br>The Gift Up! plugin requires that your PHP version is 5.6+ and cURL supports TLS1.2.<br>';
                echo 'Please conduct a TLS 1.2 Compatibility Test via <a href="https://wordpress.org/plugins/tls-1-2-compatibility-test/" target="_blank">this plugin</a>';
            }
            echo '</strong>';
            echo '</p>';
            echo '</div>';
        }

        return new giftup_api_response( $response );
    }
}
