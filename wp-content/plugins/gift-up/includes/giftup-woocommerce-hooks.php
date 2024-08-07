<?php

if ( giftup_options::has_api_key() ) {
    if ( giftup_options::get_woocommerce_enabled() ) {
        $cart_adjustment_priority = 100;

        // Compatability with WooCommerce Extended Coupon Features PRO
        if ( class_exists( 'WJECF_AutoCoupon' ) ) {
            $cart_adjustment_priority = 20;
        }

        // Compatability with WooCommerce AvaTax
        if ( class_exists( 'WC_AvaTax_Checkout_Handler' ) ) {
            $cart_adjustment_priority = 1000;
        }

        // Compatability with Advanced Dynamic Pricing (AlgolPlus)
        add_filter( 'wdp_calculate_totals_hook_priority', function( $priority ) use ( $cart_adjustment_priority ) { return $cart_adjustment_priority - 1; });

        // Adjust the cart total to take into consideration the gift card balance
        add_action( 'woocommerce_after_calculate_totals', 'giftup_woocommerce_after_calculate_totals', $cart_adjustment_priority, 1 );
        
        // Entering the gift card code on the cart
        add_action( 'woocommerce_cart_totals_before_order_total', 'giftup_woocommerce_cart_coupon' );

        // Displaying the gift card code on the checkout/payment page
        add_action( 'woocommerce_review_order_before_order_total', 'giftup_woocommerce_cart_coupon' );

        // Adding the gift card code to the order meta data
        add_action( 'woocommerce_checkout_create_order', 'giftup_woocommerce_checkout_create_order' );

        // Remove the gift card
        add_action( 'woocommerce_cart_emptied', 'giftup_woocommerce_cart_emptied' );

        // Deduct the balance from the gift card
        add_action( 'woocommerce_pre_payment_complete', 'giftup_woocommerce_redeem_gift_card' );
        add_action( 'woocommerce_order_status_processing', 'giftup_woocommerce_redeem_gift_card' );
        add_action( 'woocommerce_order_status_pre-ordered', 'giftup_woocommerce_redeem_gift_card' );
        add_action( 'woocommerce_order_status_completed', 'giftup_woocommerce_redeem_gift_card' );
        add_action( 'woocommerce_payment_complete', 'giftup_woocommerce_redeem_gift_card' );

        // Diagnostics output
        add_action( 'wp_footer', array( 'giftup_diagnostics', 'render') );

        add_action( 'wp_enqueue_scripts', 'giftup_cart_scripts' );

        // Coupon not found - try it as a gift card
        // Disabled as causes tons of API calls when a site gets trawled by a bot for working codes
        // add_filter( 'woocommerce_coupon_error', 'giftup_woocommerce_coupon_error', 30, 3 );
    }

    // Rendering the gift card that has been used in an order in the admin order details page
    add_action( 'woocommerce_admin_order_totals_after_tax', 'giftup_woocommerce_admin_order_totals_after_tax' );

    // Rendering the gift card that has been used in an order in the customers view post payment & emails
    add_filter( 'woocommerce_get_order_item_totals', 'giftup_woocommerce_get_order_item_totals', 30, 3 );
}

function giftup_cart_scripts() {
    wp_enqueue_script( 'giftup-cart-script', plugins_url( 'gift-up' ) . '/view/giftup-cart.js',  array(), GIFTUP_VERSION );
}

function giftup_woocommerce_coupon_error( $err, $err_code, $coupon ) {
    $debug = giftup_options::get_woocommerce_diagnostics_mode();

    if ( $debug ) {
        giftup_diagnostics::append( "├ Coupon not found, trying Gift Up" );
    }

    try {
        $gift_card = giftup_api::get_gift_card( $coupon->get_code() );

        if ( $gift_card !== null ) {
            giftup_cache::set_gift_card_code( $coupon->get_code() );
            
            $err = "";
            $err_code = 200;
    
            if ( is_ajax() ) {
                $err = "Coupon not found, but we found a gift card and added that to your cart instead…";
            }
        }
    } catch (exception $e) {
        return $err;
    }

    return $err;
}

/*
    Adjust the cart total to take into consideration the gift card balance
    This gets called on the basket page, on the checkoput page and before the order is created
*/
function giftup_woocommerce_after_calculate_totals( $cart ) {
    $debug = giftup_options::get_woocommerce_diagnostics_mode();

    giftup_diagnostics::new_group();
    giftup_diagnostics::append( "Entering giftup_woocommerce_after_calculate_totals…" );

    if ( property_exists( $cart, 'recurring_cart_key' ) ) {
        giftup_diagnostics::append( "└ recurring_cart_key property exists, exiting." );
        return;
    }

    if ( $debug ) {
        giftup_diagnostics::append( "├ Incoming cart sub-total: " . strval($cart->get_subtotal()) );
        giftup_diagnostics::append( "├ Incoming cart shipping: " . strval($cart->get_shipping_total()) );
        giftup_diagnostics::append( "├ Incoming cart taxes: " . strval($cart->get_total_tax()) );
        giftup_diagnostics::append( "├ Incoming cart total: " . strval($cart->total) );
    }

    if ( is_cart() || is_checkout() ) {
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['giftup_gift_card_code']) ) {
            if (strlen( $_POST['giftup_gift_card_code'] ) > 0) {
                giftup_diagnostics::append( "├ Setting gift card code to: " . $_POST['giftup_gift_card_code'] );
            } else {
                giftup_diagnostics::append( "├ Clearing gift card code" );
            }

            giftup_cache::set_gift_card_code( $_POST['giftup_gift_card_code'] );
        }
    }

    if ( !giftup_cache::get_gift_card_code() ) {
        giftup_diagnostics::append( "└ No gift card code entered, exiting." );
        return;
    }

    giftup_diagnostics::append( "├ Gift card code: " . giftup_cache::get_gift_card_code() );

    if ( giftup_api::get_is_currency_backed() == false ) {
        giftup_diagnostics::append( "└ Cannot accept non-currency-backed gift cards in WooCommerce, exiting." );
        return;
    }

    $gift_card_balance = giftup_api::get_gift_card_balance();

    if ( $gift_card_balance <= 0 ) {
        giftup_diagnostics::append( "└ Gift card has no balance, exiting." );
        return;
    }

    // Deal with this hook being replayed in the same request
    if ( property_exists( $cart, 'giftup_new_calculated_total' ) ) {
        if ( $debug ) {
            giftup_diagnostics::append( "├ Replaying compute request…" );
            giftup_diagnostics::append( "├ Previous incoming cart total: " . strval($cart->giftup_previous_incoming_cart_total) );
            giftup_diagnostics::append( "├ Previous applied balance: " . strval($cart->giftup_applied_gift_card_balance) );
            giftup_diagnostics::append( "├ Previous adjusted cart total: " . strval($cart->giftup_new_calculated_total) );
        }

        if ( abs($cart->giftup_new_calculated_total - $cart->total) < 0.00001 ) {
            giftup_diagnostics::append( "└ Cart totals are the same: " . strval($cart->total) . ", exiting." );
            return;
        } 

        giftup_diagnostics::append( "└ Cart totals differ by: " . strval($cart->giftup_new_calculated_total - $cart->total) );
    }

    $eligible_cart_total = $cart->total;

    if ( giftup_options::get_woocommerce_apply_to_shipping() == false ) {
        $eligible_cart_total = $eligible_cart_total - $cart->get_shipping_total();
        giftup_diagnostics::append( "├ Applying gift card to shipping" );
    }

    if ( giftup_options::get_woocommerce_apply_to_taxes() == false ) {
        $eligible_cart_total = $eligible_cart_total - $cart->get_total_tax();
        giftup_diagnostics::append( "├ Applying gift card to taxes" );
    }
    
    // Allow devs to mutate the cart total to filter items our etc...
    $eligible_cart_total = apply_filters( 'giftup_eligible_cart_total', $eligible_cart_total, $cart );

    // Make sure we don't set the cart total negative
    $applied_gift_card_balance = min($eligible_cart_total , $gift_card_balance);

    giftup_diagnostics::append( "├ Eligible cart total: " . strval($eligible_cart_total) );
    giftup_diagnostics::append( "├ Gift card balance: " . strval($gift_card_balance) );

    if ( $cart->total < $applied_gift_card_balance ) {
        $applied_gift_card_balance = $cart->total;
    }

    $new_cart_total = $cart->total - $applied_gift_card_balance;

    giftup_diagnostics::append( "├ Applied balance: " . strval($applied_gift_card_balance) );
    giftup_diagnostics::append( "└ New cart total: " . strval($new_cart_total) );

    // Store some data about totals for replayability guard check
    $cart->giftup_previous_incoming_cart_total = $cart->total;
    $cart->giftup_new_calculated_total = $new_cart_total;
    $cart->giftup_applied_gift_card_balance = $applied_gift_card_balance;

    $cart->set_total($new_cart_total);

    try {
        $cart->set_session();
    } catch (exception $e) {
        if ( $debug ) {
            giftup_diagnostics::append( "├ Exception calling cart->set_session()" );
            giftup_diagnostics::append( "├ " . $e->getMessage() );
        }
    }

    // Store the gift card applied balance
    giftup_cache::$applied_gift_card_balance = $applied_gift_card_balance;

    giftup_diagnostics::append( "Exiting giftup_woocommerce_after_calculate_totals…" );
}

/*
    Rendering the gift card that has been used in an order in the admin order details page
    This is the admin view when viewing an order. It is not translated.
*/
function giftup_woocommerce_admin_order_totals_after_tax ( $order_id ) {
    $order = wc_get_order( $order_id );

    if ( $order -> meta_exists(GIFTUP_ORDER_META_CODE_KEY) ) {
        $code = trim( $order -> get_meta(GIFTUP_ORDER_META_CODE_KEY) );
        $requested_balance = $order -> get_meta(GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY);
        $outstanding = $requested_balance;

        if ( $order -> meta_exists(GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY) ) {
            $redeemed_balance = $order -> get_meta(GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY);
            $outstanding = $requested_balance - $redeemed_balance;
        }

        if ( $requested_balance > 0 ) {
            ?>
            <tr>
                <td class="label">Gift card (<a href="https://giftup.app/orders?search=<?php echo $code; // WPCS: XSS ok. ?>" target="_blank" style="text-transform: uppercase"><?php echo $code; // WPCS: XSS ok. ?></a>):</td>
                <td width="1%"></td>
                <td class="total">
                    <?php echo wc_price( -1 * $requested_balance, array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok. ?>
                </td>
            </tr>
            <?php
            if ( $outstanding > 0 && $order -> get_status() == "on-hold" ) {
                ?>
                <tr>
                    <td colspan="3" style="color: red">
                        Please ensure 
                        <?php echo wc_price( $outstanding, array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok. ?>
                        has been redeemed from the gift card in Gift Up!
                    </td>
                </tr>
                <?php
            }
        }  
    }
}

/*
    Rendering the gift card that has been used in an order in the customers view post payment & emails
    This is the customers view when viewing an order in My Account and in the emails & receipt
*/
function giftup_woocommerce_get_order_item_totals( $total_rows, $order, $tax_display ) {
    if ( $order -> meta_exists(GIFTUP_ORDER_META_CODE_KEY) ) {
        $code = $order -> get_meta(GIFTUP_ORDER_META_CODE_KEY);
        $applied_balance = $order -> get_meta(GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY);

        if ( $applied_balance > 0 ) {
            // Set last total row in a variable and remove it.
            $grand_total = $total_rows['order_total'];
            unset( $total_rows['order_total'] );
        
            // Insert a new row
            $total_rows['giftup'] = array(
                'label' => __( 'Gift card', 'gift-up' ) . ' (' . strtoupper( $code ) . '):',
                'value' => wc_price( -1 * $applied_balance, array( 'currency' => $order->get_currency() ) ),
            );

            // Set back last total row
            $total_rows['order_total'] = $grand_total;
        }
    }

    return $total_rows;
}

/*
    Store the code & requested balance as soon as we have a new order created
*/
function giftup_woocommerce_checkout_create_order( $order ) {
    $code = giftup_cache::get_gift_card_code();

    if ( isset($code) ) {
        $applied_balance = giftup_cache::$applied_gift_card_balance;

        if ( $applied_balance > 0 ) {
            $order -> add_meta_data( GIFTUP_ORDER_META_CODE_KEY, $code );
            $order -> add_meta_data( GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY, $applied_balance );
            $order -> add_meta_data( GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY, 0 );
        }
    }
}

/*
    Remove the gift card code from the checkout as soon as the order is placed
*/
function giftup_woocommerce_cart_emptied() {
    giftup_cache::set_gift_card_code( null );
}

/*
    Deduct the balance from the gift card once 'payment has been made' (or for Cash On Delivery type payments, when it's set to 'processing')
    This method is safe to repeatedly call
*/
function giftup_woocommerce_redeem_gift_card( $order_id ) {
    $order = wc_get_order( $order_id );

    if ( $order -> meta_exists(GIFTUP_ORDER_META_CODE_KEY) ) {
        $code = $order -> get_meta(GIFTUP_ORDER_META_CODE_KEY);
        $requested_balance = $order -> get_meta(GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY);
        $redeemed_balance = $order -> get_meta(GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY);
        $outstanding = $requested_balance - $redeemed_balance;

        if ( $outstanding > 0 ) {
            $just_redeemed_value = giftup_api::redeem_gift_card( $code, $outstanding, $order_id );
            
            if ( $just_redeemed_value > 0 ) {
                $order -> update_meta_data( GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY, $just_redeemed_value + $redeemed_balance );
                $order -> add_order_note( wc_price( $just_redeemed_value, array( 'currency' => $order->get_currency() ) ) . " redeemed from gift card " . strtoupper( $code ) . "." );
                $order -> save();
            }

            giftup_woocommerce_possibly_hold_order( $order_id, $just_redeemed_value );
        }
    }
}

/*
    If we've had an exceptional reason to put the order on hold
    This only really occurs when there either:
    1) Not enough balance on the gift card after all (i.e. bad actor)
    2) There was a transient API call failure
*/
function giftup_woocommerce_possibly_hold_order( $order_id, $just_redeemed_value ) {
    $order = wc_get_order( $order_id );

    if ( $order -> meta_exists(GIFTUP_ORDER_META_CODE_KEY) ) {
        $code = $order -> get_meta(GIFTUP_ORDER_META_CODE_KEY);
        $requested_balance = $order -> get_meta(GIFTUP_ORDER_META_REQUESTED_BALANCE_KEY);
        $redeemed_balance = $order -> get_meta(GIFTUP_ORDER_META_REDEEMED_BALANCE_KEY);
        $outstanding = $requested_balance - $redeemed_balance;

        if ( $outstanding > 0 ) {
            if ( $redeemed_balance > 0 ) {
                $order -> add_order_note( "Gift card \"" . strtoupper( $code ) . "\" was not redeemed correctly. Only " . wc_price( $redeemed_balance, array( 'currency' => $order->get_currency() ) ) . " was redeemed off the gift card. Please redeem a further " . wc_price( $outstanding, array( 'currency' => $order->get_currency() ) ) . " from the gift card in Gift Up!" );
                $order -> save();
            }
            else {
                if ( $just_redeemed_value == -1001 ) {
                     $order -> add_order_note( "Gift card \"" . strtoupper( $code ) . "\" was not redeemed as the gift card could not be found in Gift Up." );
                } else if ( $just_redeemed_value == -1002 ) {
                     $current_balance = giftup_api::get_gift_card_balance( $code );
                     $order -> add_order_note( "Gift card \"" . strtoupper( $code ) . "\" was not redeemed as the gift card does not have enough remaining balance (" . wc_price( $current_balance, array( 'currency' => $order->get_currency() ) ) . ")." );
                } else if ( $just_redeemed_value <= -2000 ) {
                    $response_code = abs($just_redeemed_value + 2000);
                    $order -> add_order_note( "Gift card \"" . strtoupper( $code ) . "\" was not redeemed as the Gift Up plugin -> Gift Up API call failed (code: " . $response_code . "). This is either due to a temporary issue with Gift Up's API, or possibly due to a security plugin/system installed in your WordPress environment. Please review your Gift Up's API logs here: https://giftup.app/integrations/api-logs" );
                } else {
                    $order -> add_order_note( "Gift card \"" . strtoupper( $code ) . "\" was not redeemed (unknown reason: " . $just_redeemed_value . ")." );
                }

                $order -> save();
            }

            $status = $order -> get_status();

            if ( $status != 'on-hold' && $status != 'completed' ) {
                $order -> set_status('on-hold');
                $order -> save();
            }
        }
    }
}
