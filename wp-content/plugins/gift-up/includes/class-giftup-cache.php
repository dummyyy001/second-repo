<?php

class giftup_cache
{
    // Cache API gift card get API result per web request
    public static $giftcard = null;

    // Cache some cart totals
    public static $applied_gift_card_balance = 0;

    public static function get_gift_card_code() {
        if (WC()->session) {
            return WC()->session->get( GIFTUP_SESSION_KEY );
        }
        return null;
    }

    public static function set_gift_card_code( $code ) {
        if (WC()->session) {
            if (empty($code) || $code == null) {
                WC()->session->__unset( GIFTUP_SESSION_KEY );
            }
            else if (giftup_api::get_gift_card($code) != null) {
                WC()->session->set( GIFTUP_SESSION_KEY, $code );
            }
        }
    }
}
