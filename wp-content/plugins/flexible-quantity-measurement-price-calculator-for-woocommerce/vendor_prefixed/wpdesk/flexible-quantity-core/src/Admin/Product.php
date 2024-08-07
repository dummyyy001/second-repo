<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin;

use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
use WP_Post;
/**
 * Product Data Panel - General Tab
 */
class Product implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    public function hooks()
    {
        \add_action('woocommerce_product_options_dimensions', [$this, 'product_options_dimensions']);
        \add_action('woocommerce_process_product_meta', [$this, 'process_product_meta']);
        \add_action('woocommerce_product_options_pricing', [$this, 'product_minimum_price']);
        \add_action('woocommerce_process_product_meta', [$this, 'product_minimum_price_save']);
    }
    /**
     * Display our custom product Area/Volume meta fields in the product edit page
     */
    public function product_options_dimensions()
    {
        \woocommerce_wp_text_input(['id' => '_area', 'class' => 'wc_input_decimal', 'label' => \__('Area', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . ' (' . \get_option('woocommerce_area_unit') . ')', 'description' => '<br />' . \__('Overrides the area calculated from the width/length dimensions for the Measurements Price Calculator.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . '</br>']);
        \woocommerce_wp_text_input(['id' => '_volume', 'class' => 'wc_input_decimal', 'label' => \__('Volume', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . ' (' . \get_option('woocommerce_volume_unit') . ')', 'description' => '<br />' . \__('Overrides the volume calculated from the width/length/height dimensions for the Measurements Price Calculator.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . '</br>']);
    }
    /**
     * Save our custom product meta fields
     *
     * @param int $post_id The post ID.
     */
    public function process_product_meta(int $post_id)
    {
        $is_virtual = isset($_POST['_virtual']) ? 'yes' : 'no';
        $area = \wc_clean(\wp_unslash(isset($_POST['_area']) && 'no' === $is_virtual ? $_POST['_area'] : ''));
        $volume = \wc_clean(\wp_unslash(isset($_POST['_volume']) && 'no' === $is_virtual ? $_POST['_volume'] : ''));
        // Dimensions
        \update_post_meta($post_id, '_area', $area);
        \update_post_meta($post_id, '_volume', $volume);
        // compensate for non-integral stock quantities enforced by WC core
        $product_type = \wc_clean(\wp_unslash(empty($_POST['product-type']) ? 'simple' : $_POST['product-type']));
        if ('yes' === \get_option('woocommerce_manage_stock')) {
            if (!empty($_POST['_manage_stock']) && 'grouped' !== $product_type && 'variable' !== $product_type && 'external' !== $product_type) {
                $stock = (float) \wc_clean(\wp_unslash($_POST['_stock']));
                // Manage stock
                \update_post_meta($post_id, '_stock', $stock);
                // Check stock level (allowing stock quantities between 0 and 1 to be accepted, ie 0.5
                if ('variable' !== $product_type && 'no' === $_POST['_backorders'] && $_POST['_stock'] < 1 && $_POST['_stock'] > 0) {
                    $stock_status = \wc_clean(\wp_unslash($_POST['_stock_status']));
                    \update_post_meta($post_id, '_stock_status', $stock_status);
                }
            }
        }
    }
    /**
     * Display the minimum price field for simple pricing calculator products
     *
     * @since 3.1
     */
    public function product_minimum_price()
    {
        \woocommerce_wp_text_input([
            'id' => '_fq_price_calculator_min_price',
            'wrapper_class' => 'show_if_pricing_calculator',
            'class' => 'wc_input_price short',
            /* translators: Placeholders: %s - currency symbol */
            'label' => \sprintf(\__('Minimum Price (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \get_woocommerce_currency_symbol()),
            'type' => 'number',
            'custom_attributes' => ['step' => 'any', 'min' => '0'],
        ]);
    }
    /**
     * save the minimum price field for simple products
     *
     * @param int $post_id The post ID of the product being saved.
     *
     * @since 3.1
     */
    public function product_minimum_price_save(int $post_id)
    {
        if (isset($_POST['_fq_price_calculator_min_price'])) {
            $min_price = \wc_clean(\wp_unslash($_POST['_fq_price_calculator_min_price']));
            \update_post_meta($post_id, '_fq_price_calculator_min_price', $min_price);
        }
    }
}
