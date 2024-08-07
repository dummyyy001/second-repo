<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WC_Product;
use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
class ProductLoop implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * Construct and initialize the class
     *
     * @since 3.0
     */
    public function hooks()
    {
        \add_filter('woocommerce_loop_add_to_cart_link', [$this, 'loop_add_to_cart_link'], 10, 2);
    }
    /** Frontend methods ******************************************************/
    /**
     * Modify the 'add to cart' url for pricing calculator products to simply link to
     * the product page, just like a variable product.  This is because the
     * customer must supply whatever product measurements they require.
     *
     * @param string     $tag     the 'add to cart' button tag html
     * @param WC_Product $product the product
     *
     * @return string the Add to Cart tag
     * @since 3.3
     */
    public function loop_add_to_cart_link(string $tag, \WC_Product $product) : string
    {
        // Otherwise, for simple type products, the page javascript would take over and try to do an ajax add-to-cart, when really we need the customer to visit the product page to supply whatever input fields they require.
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_enabled($product) && $product->is_in_stock()) {
            /**
             * Filters the product loop URL if product is in stock and pricing calculator is enabled.
             *
             * @since 3.13.6
             *
             * @param string $product_url product URL
             * @param WC_Product $product current product
             */
            $product_url = (string) \apply_filters('fq_price_calculator_product_loop_url', \get_permalink($product->get_id()), $product);
            $tag = \sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button product_type_%s">%s</a>', \esc_url($product_url), \esc_attr($product->get_id()), \esc_attr($product->get_sku()), 'variable', \__('Select options', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
        }
        return $tag;
    }
}
