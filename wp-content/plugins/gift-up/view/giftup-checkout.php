<?php

// [giftup]
function giftup_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'domain' => '',
        'company' => '',
        'product' => '',
        'group' => '',
        'payment' => '',
        'language' => '',
        'purchasername' => '',
        'purchaseremail' => '',
        'recipientname' => '',
        'recipientemail' => '',
        'whofor' => '',
        'step' => '',
        'promocode' => '',
        'hideartwork' => '',
        'hidegroups' => '',
        'hideungroupeditems' => '',
        'hidecustomvalue' => '',
        'customvalueamount' => '',
        'referrer' => ''
    ), $atts );
    
    $companyId = sanitize_text_field( $a['company'] );

    // Quotation sanitization
    $companyId = str_replace( "“", "", $companyId );
    $companyId = str_replace( "”", "", $companyId );
    $companyId = str_replace( "'", "", $companyId );
    $companyId = str_replace( "„", "", $companyId );
    $companyId = str_replace( "‘", "", $companyId );
    $companyId = str_replace( "’", "", $companyId );
    $companyId = str_replace( "‚", "", $companyId );

    if ( strlen($companyId) != 36 ) {
        $companyId = trim( giftup_options::get_company_id() );
    }

    if ( strlen($companyId) == 36 ) {
        ob_start();
        
        ?><div class="gift-up-target" 
            data-site-id="<?php echo esc_attr($companyId) ?>" 
            data-domain="<?php echo esc_attr($a['domain']) ?>"
            data-product-id="<?php echo esc_attr($a['product']) ?>"
            data-group-id="<?php echo esc_attr($a['group']) ?>"
            data-payment-methods="<?php echo esc_attr($a['payment']) ?>"
            data-language="<?php echo esc_attr($a['language']) ?>"
            data-purchaser-name="<?php echo esc_attr($a['purchasername']) ?>"
            data-purchaser-email="<?php echo esc_attr($a['purchaseremail']) ?>"
            data-recipient-name="<?php echo esc_attr($a['recipientname']) ?>"
            data-recipient-email="<?php echo esc_attr($a['recipientemail']) ?>"
            data-step="<?php echo esc_attr($a['step']) ?>"
            data-who-for="<?php echo esc_attr($a['whofor']) ?>"
            data-promo-code="<?php echo esc_attr($a['promocode']) ?>"
            data-hide-artwork="<?php echo esc_attr($a['hideartwork']) ?>"
            data-hide-groups="<?php echo esc_attr($a['hidegroups']) ?>"
            data-hide-ungrouped-items="<?php echo esc_attr($a['hideungroupeditems']) ?>"
            data-hide-custom-value="<?php echo esc_attr($a['hidecustomvalue']) ?>"
            data-custom-value-amount="<?php echo esc_attr($a['customvalueamount']) ?>"
            data-referrer="<?php echo esc_attr($a['referrer']) ?>"
            data-platform="Wordpress"
        ></div>
        <script type="text/javascript">
        (function (g, i, f, t, u, p, s) {
            g[u] = g[u] || function() { (g[u].q = g[u].q || []).push(arguments) };
            p = i.createElement(f);
            p.async = 1;
            p.src = t;
            s = i.getElementsByTagName(f)[0];
            s.parentNode.insertBefore(p, s);
        })(window, document, 'script', 'https://cdn.giftup.app/dist/gift-up.js', 'giftup');
        </script><?php
        
        return ob_get_clean();
    }
    
    return "Notice to site admin: Please connect your Gift Up! account to WordPress in Settings / Gift Up!";
}
