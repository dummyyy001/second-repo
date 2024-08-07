<?php

class giftup_options
{
    const WOO_MODE_DISCOUNT_COUPONS = 'DISCOUNT_COUPONS';
    const WOO_MODE_API = 'API';

    public static function has_api_key() {
        if (self::get_api_key()) {
            return true;
        }

        return false;
    }

    public static function get_version() {
        return self::get_option( "version" );
    }
    public static function set_version( $value ) {
        return self::update_option( "version", $value );
    }

    public static function get_company_id() {
        return self::get_option( "company_id" );
    }
    public static function set_company_id( $value ) {
        return self::update_option( "company_id", $value );
    }

    public static function get_api_key() {
        $key = self::get_option( "api_key" );
        return apply_filters('giftup_api_key', $key, 10);
    }
    public static function set_api_key( $value ) {
        return self::update_option( "api_key", $value );
    }

    public static function get_woocommerce_enabled() {
        return self::get_option( "woocommerce_enabled" );
    }
    public static function set_woocommerce_enabled( $value ) {
        return self::update_option( "woocommerce_enabled", $value );
    }

    public static function get_woocommerce_apply_to_shipping() {
        return self::get_option( "woocommerce_apply_to_shipping" );
    }
    public static function set_woocommerce_apply_to_shipping( $value ) {
        return self::update_option( "woocommerce_apply_to_shipping", $value );
    }

    public static function get_woocommerce_apply_to_taxes() {
        return self::get_option( "woocommerce_apply_to_taxes" );
    }
    public static function set_woocommerce_apply_to_taxes( $value ) {
        return self::update_option( "woocommerce_apply_to_taxes", $value );
    }

    public static function get_woocommerce_is_in_test_mode() {
        return current_user_can('administrator') && isset($_COOKIE["giftup_test_mode"]) && $_COOKIE["giftup_test_mode"] == "test";
    }

    public static function get_woocommerce_test_mode_cookie_set() {
        return isset($_COOKIE["giftup_test_mode"]) && $_COOKIE["giftup_test_mode"] == "test";
    }

    public static function get_woocommerce_diagnostics_mode() {
        return isset($_COOKIE["giftup_diagnostics_mode"]) && $_COOKIE["giftup_diagnostics_mode"] == "on";
    }

    // Can be either 'DISCOUNT_COUPONS' or 'API'
    public static function get_woocommerce_operating_mode() {
        $setting = self::get_option( "woocommerce_operating_mode" );

        if ($setting == null || strlen($setting) == 0) {
            return self::WOO_MODE_DISCOUNT_COUPONS;
        }

        return $setting;
    }
    public static function set_woocommerce_operating_mode( $value ) {
        return self::update_option( "woocommerce_operating_mode", $value );
    }

    public static function disconnect() {
        giftup_api::notify_disconnect_woocommerce();
        giftup_settings::delete_woocommerce_webhook();

        delete_option( "giftup_company_id" );
        delete_option( "giftup_api_key" );
        delete_option( "giftup_version" );
        delete_option( "giftup_woocommerce_operating_mode" );
        delete_option( "giftup_woocommerce_enabled" );
        delete_option( "giftup_woocommerce_apply_to_shipping" );
        delete_option( "giftup_woocommerce_apply_to_taxes" );
    }

    public static function upgrade_from_v1() {
        // Fix options so that they have a giftup_ prefix due to a v1 plugin bug
        if ( !get_option( "giftup_company_id" ) && get_option( "company_id" )) {
            update_option ( "giftup_company_id", get_option( "company_id" ));
            delete_option( "company_id" );

            update_option ( "giftup_api_key", get_option( "api_key" ));
            delete_option( "api_key" );
        }
    }

    private static function get_option( $option, $default = false ) {
        return get_option( "giftup_$option", $default );
    }
    private static function update_option( $option, $value ) {
        return update_option( "giftup_$option", $value );
    }
}
