<div class="wrap" style="max-width: 800px">
  <p>
    <a href="<?php echo esc_attr($giftup_dashboard_root) ?>" target="_blank">
      <img src="<?php echo esc_attr(plugins_url( '/images/logo.png', __FILE__ )) ?>" width="150" alt="Gift Up" />
    </a>
  </p>

  <script>
  var nag = document.getElementById('giftup-nag');
  if (nag) {
    nag.style.display='none';
  }
  </script>
  
  <?php if ( isset( $message ) ): ?>
    <div id="message" class="<?php echo esc_attr($status) ?>">
      <p>
        <strong><?php echo $message ?></strong>
      </p>
    </div>
  <?php endif; ?>
  
  <?php if ( strlen($giftup_company_id) > 0 ): ?>

    <h1 class="giftup-settings-top-header">Gift Up account connected</h1>

    <?php if ( $giftup_company['onboardingCompleted'] ): ?>

      <p style="color: #46b450">
        <span class="dashicons dashicons-yes"></span> 
        You've successfully connected your <?php echo esc_html($giftup_company['name']) ?> Gift Up account to Wordpress and you're now selling gift cards.
      </p>

      <p style="padding-top: 0.5rem;">
        <a href="<?php echo esc_attr($giftup_dashboard_root) ?>" target="_blank" class="button button-primary"><?php _e('View the Gift Up dashboard') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
        <a href="https://help.giftup.com/" target="_blank" class="button"><?php _e('Gift Up help center') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
      </p>

    <?php else: ?>

      <div class="notice notice-warning" style="margin-top: 1rem">
        <p>You've successfully connected your <?php echo esc_html($giftup_company['name']) ?> Gift Up account to Wordpress, but you need to do a few more steps before you are selling gift cards ...</p>
        <ol>
          <li><a href="<?php echo esc_attr($giftup_dashboard_root) ?>/welcome" target="_blank" <?php if ( $giftup_company['canShowCheckout'] ): ?>style="text-decoration: line-through;"<?php endif; ?>>Complete the Gift Up account setup process</a><?php if ( $giftup_company['canShowCheckout'] ): ?> (completed)<?php endif; ?></li>
          <li>Insert our shortcode <code>[giftup company="<?php echo esc_attr($giftup_company['id']) ?>"]</code> anywhere on a post or a page. This will render our checkout enabling your customers to buy your gift cards.</li>
        </ol>

        <?php if ( $giftup_company['isCheckoutLive'] ): ?>
          <p style="padding-top: 0.5rem;">
            <a href="<?php echo esc_attr($giftup_dashboard_root) ?>" target="_blank" class="button button-primary"><?php _e('View your Gift Up dashboard') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
            <a href="https://help.giftup.com/" target="_blank" class="button"><?php _e('Gift Up help center') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
          </p>
        <?php else: ?>
          <p style="padding-top: 0.5rem;">
            <a href="<?php echo esc_attr($giftup_dashboard_root) ?>/welcome" target="_blank" class="button button-primary"><?php _e('Continue setting up your Gift Up account...') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
            <a href="https://help.giftup.com/" target="_blank" class="button"><?php _e('Gift Up help center') ?> <span style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;" class="dashicons dashicons-external"></span></a>
          </p>
        <?php endif; ?>
      </div>

    <?php endif; ?>

    <?php if ( $woocommerce_installed ): ?>

      <p>&nbsp;</p>
      <hr>
      <p>&nbsp;</p>

      <h3 class="giftup-settings-top-header">
        WooCommerce + Gift Up integration 
        <?php if ( !$woocommerce_activated || !$woocommerce_version_compatible ): ?>
          <span style="color: #dc3232">is not available</span>
        <?php elseif ( $woocommerce_enabled ): ?>
          <span style="color: #46b450">is enabled</span>
        <?php else: ?>
          <span style="color: darkorange">is disabled</span>
        <?php endif; ?>
        <?php if ( $woocommerce_enabled && $woocommerce_upgrade_required ): ?> (update required)<?php endif; ?>
      </h3>

      <p>
        Gift Up supports WooCommerce, enabling your customers to spend their gift cards inside of your WooCommerce cart.
        <br>This means that when you sell a gift card, your customers can use their gift card in your shoping cart.
        <a href="https://help.giftup.com/article/87-woocommerce" target="_blank">Learn more...</a>
        <?php if ( $woocommerce_legacy_method ): ?>
          <br><br>You are currently operating in our legacy mode where we sync gift cards as discount coupons.
        <?php endif; ?>
      </p>

      <?php if ( $woocommerce_upgrade_required && $woocommerce_enabled && $woocommerce_activated ): ?>

        <p>
          <span style="color: darkorange">
            <span class="dashicons dashicons-warning"></span> 
            You've installed a new version of the Gift Up plugin and an update is required to the plugin's database.
            <br>When this is completed your custmers will have a much better experience redeeming their gift cards in your cart.
          </span>
        </p>
        <form class="form-table" name="giftup_form" id="giftup_general_settings_form" method="POST" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="giftup_update_woocommerce_operating_mode" value="true" />
          <?php wp_nonce_field('giftup_update_woocommerce_operating_mode','giftup_update_woocommerce_operating_mode_nonce'); ?>

          <p><input class="button" type="submit" name="Submit" value="<?php _e('Update now') ?>" /></p>
        </form>

      <?php else: ?>

        <p>
          <?php if ( !$woocommerce_activated ): ?>
            <span style="color: #dc3232">
              <span class="dashicons dashicons-warning"></span> 
              You need to have activated the WooCommerce plugin in order to have your gift cards redeemable in your Woocommerce cart ...
            </span>
          <?php elseif ( !$woocommerce_version_compatible ): ?>
            <span style="color: #dc3232">
              <span class="dashicons dashicons-warning"></span> 
              You need to be using WooCommerce v3 or above in order to have your gift cards redeemable in your Woocommerce cart ...
            </span>
          <?php elseif ( $woocommerce_can_enable == false ): ?>
            <span style="color: #dc3232">
              <span class="dashicons dashicons-warning"></span> 
              Your WooCommerce currency (<?php _e($woocommerce_currency) ?>) needs to match your Gift Up currency (<?php _e($giftup_currency) ?>) in order to be able to enable redemption of Gift Up gift cards in your WooCommerce checkout
            </span>
          <?php else: ?>
            <form class="form-table" name="giftup_form" method="POST" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
              <input type="hidden" name="giftup_woocommerce_settings" value="true">
              <?php wp_nonce_field('giftup_woocommerce_settings','giftup_woocommerce_settings_nonce'); ?>

              <p>
                <input type="checkbox" <?php echo $woocommerce_enabled ? 'checked' : ''; ?> name="giftup_woocommerce_enabled" id="giftup_woocommerce_enabled" value="on" onchange="document.getElementById('giftup_woocommerce_extra_settings').style.display = this.checked ? 'block' : 'none';" />
                <label for="giftup_woocommerce_enabled">Enable Gift Up gift cards to be redeemed in your WooCommerce cart</label>
              </p>
              
              <div style="display: <?php echo $woocommerce_enabled ? 'block' : 'none'; ?>" id="giftup_woocommerce_extra_settings">
                <hr>
                <h4>Allow gift card balances to be used against:</h4>
                <p>
                  <input type="checkbox" checked value="on" disabled />
                  <label>All products (always enabled)</label>
                </p>
                
                <p>
                  <input type="checkbox" <?php echo $woocommerce_apply_to_shipping ? 'checked' : ''; ?> name="woocommerce_apply_to_shipping" id="woocommerce_apply_to_shipping" value="on" />
                  <label for="woocommerce_apply_to_shipping">Shipping costs</label>
                </p>
                
                <p style="margin-bottom: 2rem;">
                  <input type="checkbox" <?php echo $woocommerce_apply_to_taxes ? 'checked' : ''; ?> name="woocommerce_apply_to_taxes" id="woocommerce_apply_to_taxes" value="on" />
                  <label for="woocommerce_apply_to_taxes">Taxes</label>
                </p>

                <?php if ( $woocommerce_can_enable_test_mode ): ?>
                  <h4>Testing redemption in your cart</h4>
                  <p>
                    We highly recommend <a href="https://help.giftup.com/article/77-how-to-place-a-test-transaction" target="_blank">placing a test order in Gift Up</a> 
                    and testing that gift cards are redeemed correctly in your shopping cart to make sure there are no conflicts with any plugins you have installed.
                    Also, placing test orders is a great way to train your team (and yourself!) on how to redeem Gift Up issued gift cards and to understand your customer's buying experience fully. 
                  </p>
                  <p>
                    <input type="checkbox" value="test" <?php echo $woocommerce_enabled_test_mode ? 'checked' : ''; ?> name="woocommerce_test_mode" id="woocommerce_test_mode" />
                    <label for="woocommerce_test_mode">Enable test mode (only for you and only for 1 hour)</label>
                  </p>

                  <?php if ( $woocommerce_enabled_test_mode ): ?>
                    <p>
                      <span style="color: darkorange">
                        <span class="dashicons dashicons-warning"></span> 
                        You have enabled test mode. You can now use a test gift card in your WooCommerce cart to experience the redemption process.
                        <br>It is only enabled for you, whilst you are logged in as a WordPress admin and will reset automatically in 1 hour.
                      </span>
                    </p>
                  <?php endif; ?>

                  <h4 style="margin-top: 2rem;">Diagnosing issues with redeeming gift cards in your cart</h4>
                  <p>
                    If you are experiencing issues with the gift card balances not being applied correctly to your cart, or any other issue, 
                    please <a href="https://help.giftup.com/article/194-diagnosing-woocommerce-issues" target="_blank">follow the instructions in this guide</a>.
                  </p>
                  <p>
                    <input type="checkbox" value="on" <?php echo $woocommerce_enabled_diagnostics_mode ? 'checked' : ''; ?> name="woocommerce_diagnostics_mode" id="woocommerce_diagnostics_mode" />
                    <label for="woocommerce_diagnostics_mode">Enable diagnostics mode for you only (do this if asked to by support@giftup.app)</label>
                  </p>

                  <?php if ( $woocommerce_enabled_diagnostics_mode ): ?>
                    <div style="color: darkorange">
                      <span class="dashicons dashicons-warning"></span> 
                      You have enabled diagnostics mode. 
                      
                      Please <a href="https://help.giftup.com/article/194-diagnosing-woocommerce-issues" target="_blank">follow the instructions in this guide</a> on what to do now. 

                      <h4>Installed plugins:</h4>
                      <div><?php echo $instaled_plugins_list ?></div>
                    </div>
                  <?php endif; ?>

                <?php endif; ?>
              </div>

              <p style="margin-top: 2rem;"><input class="button" type="submit" name="Submit" value="<?php _e('Update settings') ?>" /></p>
            </form>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    <?php endif; ?>

    <p>&nbsp;</p>
    <hr>
    <p>&nbsp;</p>

    <h3 class="giftup-settings-top-header"><?php echo _e('Disconnect Gift Up') ?></h3>
    <p>This will disconnect your Gift Up account (<?php echo esc_html($giftup_company['name']) ?>) from WordPress, meaning you will no longer be able to sell gift cards online.</p>
    <form class="form-table" name="giftup_form" id="giftup_general_settings_form" method="POST" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" onsubmit="if (confirm('Disconnect Gift Up from wordpress? This means you\'ll no longer be able to sell gift cards on your website.')) { return true; } else { return false; }">
      <?php wp_nonce_field('giftup_api_key','giftup_api_key_nonce'); ?>

      <textarea type="text" id="giftup_api_key" name="giftup_api_key" class="giftup-settings-key" rows="5" cols="40" placeholder="API Key" style="display: none;"></textarea>
      <p><input class="button" style="color: #dc3232; border-color: #dc3232" type="submit" name="Submit" value="<?php _e('Disconnect Gift Up') ?>" /></p>
    </form>

  <?php else: ?>

    <h1 class="giftup-settings-top-header"><?php echo _e('Connect to Gift Up ...') ?></h1>
    <p>In order to sell gift cards on your WordPress website, you need a free Gift Up account connected to your WordPress website. Follow the steps below ... </p>
    <p>&nbsp;</p>

    <?php if ( strlen($giftup_company_id) > 0 and strlen($giftup_api_key) > 0 ): ?>
      <div id="message" class="notice notice-error">
        <p>
          <strong>
            There has been a problem connecting to Gift Up Please refresh this page and if the connection issue still exists, please <a href="<?php echo esc_attr($giftup_dashboard_root) ?>/installation/wordpress" target="_blank">double check your API key</a>.
          </strong>
        </p>
      </div>
    <?php endif; ?>

    <ol>
      <li>
        <p>
          <input class="button button-primary" type="button" value="<?php _e('Create a new Gift Up account') ?>" 
                  onclick="window.open('<?php echo esc_attr($giftup_dashboard_root) ?>/account/register?returnUrl=/installation/wordpress&amp;email=<?php echo esc_attr($giftup_email_address) ?>')"/>
          or
          <input class="button" type="button" value="<?php _e('Log in to your existing Gift Up account') ?>" 
                  onclick="window.open('<?php echo esc_attr($giftup_dashboard_root) ?>/installation/wordpress')"/>
        </p>
      </li>
      <li>Once inside your Gift Up account, <a href="<?php echo esc_attr($giftup_dashboard_root) ?>/installation/wordpress" target="_blank">get your API key</a></li>
      <li>
        <p>Copy &amp; paste the provided Gift Up API key below:</p>
        <form class="form-table" name="giftup_form" id="giftup_general_settings_form" method="POST" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
          <?php wp_nonce_field('giftup_api_key','giftup_api_key_nonce'); ?>

          <textarea type="text" id="giftup_api_key" name="giftup_api_key" class="giftup-settings-key" rows="5" cols="40" placeholder="API Key"><?php echo ( $giftup_api_key ); ?></textarea>
          <p><input class="button button-primary" type="submit" name="Submit" value="<?php _e('Connect to Gift Up') ?>" /></p>
        </form>
      </li>
    </ol>

  <?php endif; ?>
</div>
