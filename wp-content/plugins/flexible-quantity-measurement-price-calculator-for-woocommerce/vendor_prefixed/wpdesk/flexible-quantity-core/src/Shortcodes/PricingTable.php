<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Shortcodes;

/**
 * Pricing Table Shortcode
 *
 * Displays a pricing table
 *
 * @since 3.0
 */
class PricingTable
{
    /**
     * Get the shortcode content
     *
     * @since 3.0
     * @param array $atts associative array of shortcode parameters
     * @return string shortcode content
     */
    public static function get($atts)
    {
        return \WC_Shortcodes::shortcode_wrapper([__CLASS__, 'output'], $atts, ['class' => 'wc-measurement-price-calculator']);
    }
    /**
     * Output a pricing table
     *
     * * product_id/product_sku - id or sku of product.  Defaults to current product, if any
     *
     * Usage:
     * [fq_price_calculator_pricing_table]
     *
     * @param array $atts associative array of shortcode parameters
     */
    public static function output($atts)
    {
        global $product, $wpdb;
        \extract(\shortcode_atts(['product_id' => '', 'product_sku' => ''], $atts));
        // product by sku?
        if ($product_sku) {
            $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $product_sku));
        }
        // product by id?
        if ($product_id) {
            $product = \wc_get_product($product_id);
        }
        // product ?
        if (!$product) {
            return;
        }
        // pricing rules?
        $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Shortcodes\WC_Price_Calculator_Settings($product);
        if (!$settings->pricing_rules_enabled() || !$settings->has_pricing_rules()) {
            return;
        }
        // the countdown element with a unique identifier to allow multiple countdowns on the same page, and common class for ease of styling
        echo \wp_kses_post(self::get_pricing_rules_table($settings->get_pricing_rules($settings->get_pricing_unit()), $settings));
    }
    /**
     * Returns a pricing rules HTML table
     *
     * @since 3.0
     * @param array $rules array of pricing rules
     * @param WC_Price_Calculator_Settings $settings the calculator settings object
     * @return string pricing rules HTML table
     */
    public static function get_pricing_rules_table($rules, $settings)
    {
        $html = '<table class="wc-measurement-price-calculator-pricing-table">';
        /* translators: Placeholders: %s - pricing unit */
        $html .= '<thead><tr><th>' . \sprintf(\__('Range (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), '<span class="units">' . \__($settings->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce') . '</span>') . '</th>';
        /* translators: Placeholders: %s - currency symbol */
        $html .= '<th>' . \sprintf(\__('Price (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), '<span class="units">' . \get_woocommerce_currency_symbol() . '/' . \__($settings->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce') . '</span>') . '</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($rules as $rule) {
            // format the range as "1 ft", "1 - 3 ft" or "1+ ft"
            $range = $rule['range_start'];
            if ('' === $rule['range_end']) {
                $range .= '+';
            } elseif ($rule['range_end'] != $rule['range_start']) {
                $range .= ' - ' . $rule['range_end'];
            }
            $range .= ' ' . \__($settings->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce');
            $html .= \sprintf('<tr><td>%s</td><td>%s</td></tr>', $range, $settings->get_pricing_rule_price_html($rule));
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}
