<?php

function giftup_woocommerce_cart_coupon() {
    $incomingcode = null;

    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['giftup_gift_card_code']) ) {
        $incomingcode = $_POST['giftup_gift_card_code'];
    }

    $appliedcode = giftup_cache::get_gift_card_code();
    $balance = giftup_api::get_gift_card_balance();
    $isCurrencyBacked = giftup_api::get_is_currency_backed();
    $isValid = giftup_api::get_gift_card_is_valid();

    $initial_gc_apply_state = "inline-block";
    $initial_gc_form_state = "none";
    $message = "";

    if ( isset($appliedcode) && strlen($appliedcode) > 0 && ($isValid == false || $balance <= 0 || $isCurrencyBacked == false) ) {
        $appliedcode = "";
        $initial_gc_apply_state = "none";
        $initial_gc_form_state = "grid";

        if ( $isValid == false ) {
            $message = __( 'Gift card is no longer valid', 'gift-up' );
            $appliedcode = giftup_cache::set_gift_card_code( null );

        } else if ( $isCurrencyBacked == false ) {
            $message = __( 'Gift card cannot be used as it is a unit-backed, not a currency-backed gift card', 'gift-up' );
            $appliedcode = giftup_cache::set_gift_card_code( null );

        } else if ( $balance <= 0 ) {
            $message = __( 'Gift card has no remaining balance', 'gift-up' );
            $appliedcode = giftup_cache::set_gift_card_code( null );
        }
    }
    else if ( empty($incomingcode) == false && empty($appliedcode) ) {
        $initial_gc_apply_state = "none";
        $initial_gc_form_state = "grid";
        $message =  __( 'Gift card not found', 'gift-up' );
        $appliedcode = giftup_cache::set_gift_card_code( null );
    }

    $responsive_title = __( 'Gift card', 'gift-up' );

    if ( !empty($appliedcode)) {
        $responsive_title = $responsive_title . " (" . $appliedcode . ")";
    }

    ?>
    <tr class="cart-subtotal giftup-cart-subtotal">
        <th class="giftup-cart-subtotal-th">
            <?php if ( giftup_options::get_woocommerce_is_in_test_mode() ): ?>
                [TEST]
            <?php endif; ?>
            <?php echo __( 'Gift card', 'gift-up' ) ?><?php if ( !empty($appliedcode) ): ?>: <span style="text-transform: uppercase" class="giftup-cart-subtotal-th-balance-title"><?php echo esc_html($appliedcode) ?></span><?php endif; ?>

            <?php if ( !empty($appliedcode) ): ?>
                <div class="giftup-cart-subtotal-th-balance-container" style="font-weight: normal; font-size: small; font-weight: 300;">
                    <div class="giftup-cart-subtotal-th-balance-value"><?php echo __( 'Balance', 'gift-up' ) ?>: <?php echo wc_price($balance) ?></div>
                </div>
            <?php endif; ?>
        </th>
        <td data-title="<?php echo esc_attr($responsive_title) ?>" class="giftup-cart-subtotal-td">
            <?php if ( empty($appliedcode) ): ?>
                <a href="javascript:void(0)" 
                   class="giftup-cart-subtotal-td-apply-gc"
                   style="display: <?php echo esc_attr($initial_gc_apply_state) ?>"
                   onclick="giftup_hide_apply_gift_card()">
                    <?php echo __( 'Apply gift card', 'gift-up' ) ?>
                </a>
                <div data-action="<?php echo (is_checkout() ? wc_get_checkout_url() : wc_get_cart_url()) ?>"
                     class="giftup-cart-subtotal-td-form"
                     style="display: <?php echo esc_attr($initial_gc_form_state) ?>; grid-template-columns: minmax(110px,1fr) fit-content(40px);"> 
                    <input class="giftup-cart-subtotal-td-form-input input-text"
                           type="text"
                           id="giftup-giftcard-code" 
                           value="<?php echo esc_attr($incomingcode) ?>"
                           placeholder="<?php echo __( 'Gift card code', 'gift-up' ) ?>" 
                           onkeypress="return giftup_code_keypress(event)"
                           style="display: block; margin: 0; min-width: 100px;">
                    <button class="giftup-cart-subtotal-td-form-button button wp-element-button"
                            style="cursor: pointer; margin: 0;"
                            type="button"
                            value="<?php echo __( 'Apply gift card', 'gift-up' ) ?>" style="white-space: nowrap; width: 100%; margin: 0;"
                            onclick="giftup_submit_code(event)"><?php echo __( 'Apply', 'gift-up' ) ?></button>
                </div>
                <?php if ( empty($message) == false ): ?>
                    <ul class="woocommerce-error giftup-cart-subtotal-error" style="margin-top: 1rem" role="alert">
                        <li><?php echo $message ?></li>
                    </ul>                
                <?php endif; ?>
            <?php else: ?>
                <div class="woocommerce-Price-amount amount giftup-cart-subtotal-td-applied-balance" style="font-weight: normal;">
                    <div>-<?php echo wc_price(giftup_cache::$applied_gift_card_balance) ?> [<a href="#" onclick="giftup_set_code(''); return false;"><?php echo __( 'Remove', 'gift-up' ) ?></a>]</div>
                </div>
            <?php endif; ?>
        </td>
    </tr>
    <?php if ( !giftup_options::get_woocommerce_is_in_test_mode() && giftup_options::get_woocommerce_test_mode_cookie_set() ): ?>
        <tr class="cart-subtotal giftup-cart-subtotal">
            <td colspan="2">
                <div class="giftup-cart-subtotal-error" style="color: #990000" role="alert">
                    Warning: You've requested Gift Up to be in test mode so you can test redeeming test 
                    gift cards, but you need to be logged in as an administrator to WordPress in this tab.
                </div>
            </td>
        </tr>
    <?php endif; ?>

    <?php
}
