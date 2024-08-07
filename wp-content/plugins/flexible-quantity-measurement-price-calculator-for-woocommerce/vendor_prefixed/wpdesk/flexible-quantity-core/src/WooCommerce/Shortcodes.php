<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WC_Shortcodes;
use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Shortcodes\PricingTable;
/**
 * Shortcodes handler.
 *
 * @since 3.14.0
 */
class Shortcodes implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    public function hooks()
    {
        \add_shortcode('fq_price_calculator_pricing_table', [$this, 'pricing_table_shortcode']);
    }
    /**
     * @param array $atts associative array of shortcode parameters
     *
     * @return string shortcode content
     */
    public function pricing_table_shortcode(array $atts) : string
    {
        return \WC_Shortcodes::shortcode_wrapper([\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Shortcodes\PricingTable::class, 'output'], $atts, ['class' => 'wc-measurement-price-calculator']);
    }
}
