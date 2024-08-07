<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WC_Order;
use WC_Order_Item_Product;
use WC_Product;
use WC_Product_Variable;
use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
class Inventory implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    /** @var bool used to keep track of whether a pricing calculator product stock has been updated when added to cart */
    private $pricing_stock_altered = \false;
    /**
     * Construct and initialize the inventory handling class
     *
     * @since 3.0
     */
    public function hooks()
    {
        // set the measurement stock amount when adding to the cart in WC <= 2.6
        \add_filter('woocommerce_stock_amount', [$this, 'get_measurement_stock_amount'], 5, 1);
        // set the measurement stock in WC 3.0
        \add_filter('woocommerce_add_to_cart_quantity', [$this, 'set_measurement_add_to_cart_stock_amount'], 5, 2);
        // set the measurement stock amount when updating the cart/checkout in all supported WC versions
        \add_filter('woocommerce_stock_amount_cart_item', [$this, 'get_measurement_stock_amount'], 10, 2);
        // set the measurement stock amount when adding to the cart
        \add_filter('woocommerce_add_cart_item', [$this, 'set_measurement_stock_amount'], 1, 1);
        \add_filter('woocommerce_get_availability', [$this, 'get_availability_measurement'], 10, 2);
        \add_filter('woocommerce_cart_item_quantity', [$this, 'get_cart_item_quantity'], 10, 2);
        \add_filter('woocommerce_widget_cart_item_quantity', [$this, 'get_widget_cart_item_quantity'], 10, 3);
        // note: no filter required for the order items table, as its unit quantity by then
        \add_filter('woocommerce_checkout_cart_item_quantity', [$this, 'get_checkout_item_quantity'], 10, 2);
        \add_action('woocommerce_after_cart_item_quantity_update', [$this, 'after_cart_item_quantity_update'], 10, 2);
        \add_filter('woocommerce_order_item_quantity', [$this, 'get_order_item_measurement_quantity'], 10, 3);
        \add_filter('woocommerce_order_get_items', [$this, 'order_again_item_set_quantity'], 10, 2);
        \add_filter('woocommerce_cart_shipping_packages', [$this, 'cart_shipping_packages']);
        // filter the backordered quantity item meta label to reference measurement unit
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\WooCompatibility::is_wc_version_gt('3.2')) {
            \add_filter('woocommerce_backordered_item_meta_name', [$this, 'get_backordered_item_meta_name'], 20, 2);
        } else {
            \add_filter('woocommerce_backordered_item_meta_name', [$this, 'get_backordered_item_meta_name'], 20, 1);
        }
        if (\is_admin() || \is_ajax()) {
            \add_filter('woocommerce_reduce_order_stock_quantity', [$this, 'admin_manage_order_stock'], 10, 2);
            \add_filter('woocommerce_restore_order_stock_quantity', [$this, 'admin_manage_order_stock'], 10, 2);
        }
        // filter the quantity input step for order items on admin order page
        \add_filter('woocommerce_quantity_input_step_admin', [$this, 'woocommerce_quantity_input_step_admin'], 10, 2);
    }
    /**
     * This returns the stock amount in pricing units for pricing calculator
     * products with inventory enabled.  This filter is called from a number of
     * places, but the only times that we're interested in are:
     * * when a product is added to the cart $_REQUEST['add-to-cart']
     * * when the cart is updated or we transition to the checkout page
     * * when an order is "ordered again"
     * The purpose of this is to convert the item quantity (ie 2 pieces of
     * fabric) to the measurement quantity (ie 2 pieces of fabric at 3 ft each
     * equals 6 ft of fabric)
     *
     * @param int|float   $quantity      the item quantity
     * @param string|null $cart_item_key the cart item key, available when the cart
     *                                 is being updated or we're moving to the checkout pages
     *
     * @return int|float the calculated measurement quantity
     * @since 3.0.0
     */
    public function get_measurement_stock_amount($quantity, string $cart_item_key = null)
    {
        if ($cart_item_key) {
            // This is called when updating the cart/transitioning to checkout,
            // so we already have the measurement needed in pricing/stock units.
            $cart = \WC()->cart->get_cart();
            $product = $cart[$cart_item_key]['data'];
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
            $measurement_needed = $cart[$cart_item_key]['pricing_item_meta_data']['_measurement_needed'] ?? null;
            $measurement_needed_unit = $cart[$cart_item_key]['pricing_item_meta_data']['_measurement_needed_unit'] ?? null;
            if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
                // quantity * measurement needed in pricing units
                $quantity *= \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement::convert($measurement_needed, $measurement_needed_unit, $settings->get_pricing_unit());
            }
        }
        return $quantity;
    }
    /**
     * Set the measurement stock amount in pricing units for pricing calculator products with inventory enabled.
     * The purpose of this is to convert the item quantity (ie 2 pieces of fabric)
     * to the measurement quantity (ie 2 pieces of fabric at 3 ft each equals 6 ft of fabric).
     * This is designed for WC 3.0+ and can replace the get_measurement_stock_amount()
     * method above when WC 3.0+ is required.
     *
     * @param string[] $cart_item cart item data
     *
     * @return string[] updated cart item data
     * @since 3.11.4
     */
    public function set_measurement_stock_amount(array $cart_item) : array
    {
        if ($cart_item['data'] instanceof \WC_Product && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($cart_item['data'])) {
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($cart_item['data']);
            $measurement_needed_unit = $settings->get_pricing_unit();
            $measurement_needed_value = null;
            if (isset($_REQUEST['_measurement_needed_unit'])) {
                $measurement_needed_unit = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed_unit']));
            }
            if (isset($_REQUEST['_measurement_needed'])) {
                $measurement_needed_value = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed']));
            }
            if (isset($cart_item['pricing_item_meta_data'], $cart_item['pricing_item_meta_data']['_measurement_needed']) && $measurement_needed_value !== $cart_item['pricing_item_meta_data']['_measurement_needed']) {
                $measurement_needed_value = $cart_item['pricing_item_meta_data']['_measurement_needed'];
            }
            // measurement instance
            $measurement_needed = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($measurement_needed_unit, $measurement_needed_value);
            $quantity = isset($_REQUEST['quantity']) && \is_numeric($_REQUEST['quantity']) ? \sanitize_text_field(\wp_unslash($_REQUEST['quantity'])) : 1;
            $quantity = (float) $quantity;
            // quantity * measurement needed in pricing units
            $cart_item['quantity'] = $quantity * $measurement_needed->get_value($settings->get_pricing_unit());
        }
        return $cart_item;
    }
    /**
     * Set the quantity being added to the cart in WooCommerce 3.0+ when inventory is enabled.
     * This needs to run in addition to set_measurement_stock_amount() because if quantity drops below 1, WooCommerce
     *  will throw an exception due to stock amount not being enough, and the incoming requested quantity being "1".
     * This filters quantity before the check so the requested quantity represents the measurement input.
     *
     * @param float $quantity   the quantity being added to the cart
     * @param int   $product_id the product ID being added to the cart
     *
     * @return float the updated quantity
     * @since 3.12.0
     */
    public function set_measurement_add_to_cart_stock_amount($quantity, int $product_id)
    {
        $product = \wc_get_product($product_id);
        if ($product instanceof \WC_Product && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
            $measurement_needed_unit = $settings->get_pricing_unit();
            $measurement_needed_value = null;
            if (isset($_REQUEST['_measurement_needed_unit'])) {
                $measurement_needed_unit = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed_unit']));
            }
            if (isset($_REQUEST['_measurement_needed'])) {
                $measurement_needed_value = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed']));
            }
            // Get the needed unit, but default for backwards compatibility.
            $measurement_needed = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($measurement_needed_unit, $measurement_needed_value);
            // quantity * measurement needed in pricing units
            $quantity = isset($_REQUEST['quantity']) && \is_numeric($_REQUEST['quantity']) ? \sanitize_text_field(\wp_unslash($_REQUEST['quantity'])) : 1;
            $quantity = (float) $quantity;
            $quantity = $quantity * $measurement_needed->get_value($settings->get_pricing_unit());
        }
        return $quantity;
    }
    /**
     * Gets the checkout item quantity html, modifying for pricing calculator
     * products with inventory enabled.  We replace the measurement quantity
     * (ie 10 feet) with the item unit quantity (ie 2 pieces of fabric at 5
     * feet each)
     *
     * @param string $item_quantity_html the checkout item quantity html
     * @param array  $values             the cart item data
     *
     * @return string the checkout item name html
     * @since 3.0
     */
    public function get_checkout_item_quantity($item_quantity_html, array $values) : string
    {
        $_product = $values['data'];
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($_product) && isset($values['pricing_item_meta_data']['_quantity']) && $values['pricing_item_meta_data']['_quantity']) {
            // replace the item measurement quantity (10 feet) with the item unit quantity (2 pieces of fabric)
            $item_quantity_html = '<strong class="product-quantity">&times; ' . $values['pricing_item_meta_data']['_quantity'] . '</strong>';
        }
        return $item_quantity_html;
    }
    /**
     * Filter the shipping packages, modifying the quantity for pricing
     * calculator products with inventory enabled.  We replace the measurement
     * quantity (ie 10 feet) with the item unit quantity (ie 2 pieces of fabric
     * at 5 feet each).  This is done so that shipping methods can operate on
     * the quantity of products (ie 2 pieces of fabric) with a weight that
     * corresponds to the item unit quantity.
     *
     * @param array $packages shipping packages
     *
     * @return array shipping packages
     * @since 3.2
     */
    public function cart_shipping_packages(array $packages) : array
    {
        foreach (\array_keys($packages) as $index) {
            foreach ($packages[$index]['contents'] as $package_id => $values) {
                $_product = $values['data'];
                if (isset($values['pricing_item_meta_data']['_quantity']) && $values['pricing_item_meta_data']['_quantity'] && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($_product)) {
                    // replace the item measurement quantity (10 feet) with the item unit quantity (2 pieces of fabric)
                    // That way the quantity and weight are correct for shipping methods to calculate rates
                    $packages[$index]['contents'][$package_id]['quantity'] = $values['pricing_item_meta_data']['_quantity'];
                }
            }
        }
        return $packages;
    }
    /**
     * Returns the availability of the product, including the unit if this is a
     * pricing calculator product with inventory enabled.  Ie, instead of '9 in
     * stock' this might return '9 ft. in stock'
     *
     * @param array      $return  array with keys 'availability' and 'class'
     * @param WC_Product $product product object
     *
     * @return array
     * @since 3.0
     */
    public function get_availability_measurement(array $return, \WC_Product $product) : array
    {
        $class = $return['class'];
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product) && $product->managing_stock()) {
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
            if ($product->is_in_stock()) {
                $total_stock = $this->get_total_stock($product);
                if ($total_stock > 0) {
                    $format_option = \get_option('woocommerce_stock_format');
                    switch ($format_option) {
                        case 'no_amount':
                            return $return;
                        // nothing to be done
                        case 'low_amount':
                            $low_amount = \get_option('woocommerce_notify_low_stock_amount');
                            $format = $total_stock <= $low_amount ? \__('Only %1$s %2$s left in stock', 'flexible-quantity-measurement-price-calculator-for-woocommerce') : \__('In stock', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
                            break;
                        default:
                            $format = \__('%1$s %2$s in stock', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
                            break;
                    }
                    $availability = \sprintf($format, $this->format_total_stock_number($total_stock, $product), \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_unit_label($settings->get_pricing_unit()));
                    if ($product->backorders_allowed() && $product->backorders_require_notification()) {
                        $availability .= ' ' . \__('(backorders allowed)', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
                    }
                    $return = ['availability' => $availability, 'class' => $class];
                }
            }
        }
        return $return;
    }
    /**
     * Formats a product total stock amount for display.
     *
     * @param int|float  $number  the total stock being formatted
     * @param WC_Product $product the product the stock is form
     *
     * @return string
     * @since 3.19.2
     */
    private function format_total_stock_number($number, \WC_Product $product) : string
    {
        /**
         * Filters the formatted total stock number.
         *
         * @param string     $formatted_stock   the formatted stock amount number
         * @param string     $unformatted_stock the original stock amount number
         * @param WC_Product $product           the related product
         *
         * @since 3.19.2
         */
        return (string) \apply_filters('fq_price_calculator_total_stock_formatted', \number_format($number, \wc_get_price_decimals(), \wc_get_price_decimal_separator(), \wc_get_price_thousand_separator()), $number, $product);
    }
    /**
     * Get a product total stock amount.
     *
     * @param WC_Product|WC_Product_Variable $product A product.
     *
     * @return float|int
     * @since 3.11.0
     */
    private function get_total_stock($product)
    {
        $children = $product->get_children();
        if (\count($children) > 0) {
            $total_stock = \max(0, $product->get_stock_quantity());
            foreach ($children as $child_id) {
                $child = \wc_get_product($child_id);
                if ($child && 'yes' === $child->get_meta('_manage_stock')) {
                    $stock = $child->get_meta('_stock');
                    $total_stock += \max(0, \wc_stock_amount($stock));
                }
            }
        } else {
            $total_stock = $product->get_stock_quantity();
        }
        return \wc_stock_amount($total_stock);
    }
    /**
     * Gets the item quantity HTML snippet to display in the cart, modifying if
     * the product uses the pricing calculator with inventory management enabled.
     * Replaces the measurement quantity (ie 10 feet) with the item unit
     * quantity (ie 2 pieces of fabric at 5 feet each)
     *
     * @param string $quantity_html the cart item quantity html snippet
     * @param string $cart_item_key the cart item key
     *
     * @return string the cart item quantity html snippet
     * @since 3.0
     */
    public function get_cart_item_quantity(string $quantity_html, string $cart_item_key) : string
    {
        $cart = \WC()->cart->get_cart();
        if (!isset($cart[$cart_item_key]['data'])) {
            return $quantity_html;
        }
        /** @var WC_Product $_product */
        $_product = $cart[$cart_item_key]['data'];
        if ($_product->is_sold_individually()) {
            return $quantity_html;
        }
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($_product) && isset($cart[$cart_item_key]['pricing_item_meta_data']['_quantity']) && $cart[$cart_item_key]['pricing_item_meta_data']['_quantity']) {
            $quantity_html = \woocommerce_quantity_input(['input_name' => 'cart[' . $cart_item_key . '][qty]', 'input_value' => $cart[$cart_item_key]['pricing_item_meta_data']['_quantity'], 'max_value' => \apply_filters('woocommerce_quantity_input_max', $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(), $_product)], $_product, \false);
        }
        return $quantity_html;
    }
    /**
     * Gets the item quantity HTML snippet to display in the mini-cart,
     * modifying for pricing calculator products with inventory enabled.
     * Replaces the measurement quantity (ie 10 feet) with the item unit
     * quantity (ie 2 pieces of fabric at 5 feet each)
     *
     * @param string $quantity_html the mini-cart item quantity html snippet
     * @param array  $cart_item     the cart item identified by $cart_item_key
     * @param string $cart_item_key the mini-cart item key
     *
     * @return string the mini-cart item quantity html snippet
     * @since 3.0
     */
    public function get_widget_cart_item_quantity($quantity_html, $cart_item, string $cart_item_key) : string
    {
        \WC()->cart->get_cart();
        $_product = $cart_item['data'];
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($_product) && isset($cart_item['pricing_item_meta_data']['_quantity']) && $cart_item['pricing_item_meta_data']['_quantity']) {
            $product_price = \apply_filters('woocommerce_cart_item_price', \WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
            $quantity_html = '<span class="quantity">' . \sprintf('%s &times; %s', $cart_item['pricing_item_meta_data']['_quantity'], $product_price) . '</span>';
        }
        return $quantity_html;
    }
    /**
     * Invoked after a cart item's quantity is updated, and if the item in
     * question is for a pricing calculator product with inventory enabled, and
     * it's being added to the cart or the cart is being updated or proceeding
     * to checkout, we keep track of the unit quantity which is displayed to the
     * customer.  The unit quantity would be '2' pieces of 3 foot fabric, for
     * instance.
     *
     * @param string  $cart_item_key the cart item key
     * @param numeric $quantity      the item quantity
     *
     * @since 3.0
     */
    public function after_cart_item_quantity_update(string $cart_item_key, $quantity)
    {
        $cart_items = \WC()->cart->get_cart();
        if (isset($cart_items[$cart_item_key]) && $cart_items[$cart_item_key]) {
            // we want the product, not the variation
            $product = \wc_get_product($cart_items[$cart_item_key]['product_id']);
            if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
                // save the actual item quantity (ie *2* pieces of fabric at 3 feet each)
                if (isset($_REQUEST['quantity'])) {
                    // add-to-cart actions
                    $quantity = isset($_REQUEST['quantity']) && \is_numeric($_REQUEST['quantity']) ? \sanitize_text_field(\wp_unslash($_REQUEST['quantity'])) : 1;
                    $quantity = (float) $quantity;
                    \WC()->cart->cart_contents[$cart_item_key]['pricing_item_meta_data']['_quantity'] += $quantity;
                    // in WC 3.0+, adding a 2nd quantity to the cart of a product with the same measurements (e.g. clicking add to cart twice)
                    // uses WC_Cart::set_quantity() and skips the woocommerce_add_cart_item filter which prevents us from changing the cart quantity to reflect
                    // the total measured amount (e.g. 2 pieces of fabric at 3 feet each is a total quantity of 6, when priced on a per-foot basis).
                    // This resets the cart quantity to the total measured amount. When we can require WC 3.1+, we can make use of the
                    // woocommerce_add_to_cart_quantity filter added in https://github.com/woocommerce/woocommerce/commit/494fa0974c955796a3168892499bf370aa9ce8f2
                    // to set the correct quantity prior to WC_Cart::set_quantity() being called. {MR 2017-06-21}
                    $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
                    $measurement_needed_unit = $settings->get_pricing_unit();
                    $measurement_needed_value = null;
                    if (isset($_REQUEST['_measurement_needed_unit'])) {
                        $measurement_needed_unit = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed_unit']));
                    }
                    if (isset($_REQUEST['_measurement_needed'])) {
                        $measurement_needed_value = \sanitize_text_field(\wp_unslash($_REQUEST['_measurement_needed']));
                    }
                    $measurement_needed = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($measurement_needed_unit, $measurement_needed_value);
                    // quantity * measurement needed in pricing units
                    \WC()->cart->cart_contents[$cart_item_key]['quantity'] = \WC()->cart->cart_contents[$cart_item_key]['pricing_item_meta_data']['_quantity'] * $measurement_needed->get_value($settings->get_pricing_unit());
                } elseif (!empty($_POST['update_cart']) || !empty($_POST['proceed'])) {
                    // update cart/proceed to checkout
                    if (isset($_POST['cart']) && isset($_POST['cart'][$cart_item_key]['qty'])) {
                        $qty = \wc_clean(\wp_unslash($_POST['cart'][$cart_item_key]['qty']));
                        //phpcs:ignore
                        \WC()->cart->cart_contents[$cart_item_key]['pricing_item_meta_data']['_quantity'] = \preg_replace('/[^0-9\\.]/', '', $qty);
                    }
                }
            }
        }
    }
    /**
     * Gets the measurement stock quantity for the given item if its a pricing
     * calculator item with inventory enabled, for purposes of reducing the
     * product stock.  Ie, if $quantity is 2 and the item is 3 ft fabric, the
     * measurement stock returned would be 6
     *
     * @param int|float $quantity the cart item quantity
     * @param WC_Order $order    the order object
     * @param array     $item     the order item
     *
     * @return int|float
     * @since 3.0
     */
    public function get_order_item_measurement_quantity($quantity, $order, $item)
    {
        // always need the actual parent product, not the useless variation product
        $product = \wc_get_product($item['product_id']);
        if (isset($item['item_meta']['_fq_measurement_data'][0]) && $item['item_meta']['_fq_measurement_data'][0] && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
            $measurement_data = \maybe_unserialize($item['item_meta']['_fq_measurement_data'][0]);
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
            // get the measurement quantity (ie item quantity is '2' pieces of fabric at 3 ft each, so the measurement quantity is '6'
            $quantity *= \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement::convert($measurement_data['_measurement_needed'], $measurement_data['_measurement_needed_unit'], $settings->get_pricing_unit());
        }
        return $quantity;
    }
    /**
     * Modifies the 'Backordered' order item meta name to include the units for backordered pricing calculator products
     * with inventory enabled For example: "Backordered (ft.): 12.4"
     *
     * @param string                     $backordered_text  the backordered text
     * @param null|WC_Order_Item_Product $item              the backordered item (available in callback from WC 3.2
     *                                                      onwards)
     *
     * @return string the backordered text, including units if available
     * @since 3.0
     * @internal
     */
    public function get_backordered_item_meta_name($backordered_text, $item = null)
    {
        // TODO the $item argument will be added from WC 3.2 onwards, remove hook BC when 3.2 is the minimum required version {FN 2017-07-26}
        if (empty($item)) {
            $cart_contents = \WC()->cart->get_cart();
            $regular_items = 0;
            $pricing_units = [];
            // in WC < 3.2 we have no context where the 'Backordered' label is printed so we can make some assumption based on cart contents
            foreach ($cart_contents as $key => $cart_item) {
                if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($cart_item['data'])) {
                    $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($cart_item['data']);
                    $pricing_units[] = $settings->get_pricing_unit();
                } else {
                    ++$regular_items;
                }
            }
            if (0 === $regular_items) {
                // all cart items are MPC items
                if (\count(\array_count_values($pricing_units)) > 1) {
                    // there are at least two different pricing units
                    $backordered_text = \__('Backordered measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
                } else {
                    // there is only one pricing unit being used across one or more MPC items
                    $backordered_text .= \sprintf(' (%s)', \current($pricing_units));
                }
            } elseif (\count($pricing_units) > 0) {
                // there are both regular items and one or more MPC items
                $backordered_text = \__('Backordered quantity or measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
            }
        } elseif ($item instanceof \WC_Order_Item_Product && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($item->get_product())) {
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($item->get_product());
            // in WC 3.2+ we have context to output the pricing unit for the current backordered item
            $backordered_text .= \sprintf(' (%s)', $settings->get_pricing_unit());
        }
        return $backordered_text;
    }
    /**
     * Filter to set the measurement quantity for pricing calculator type
     * products with inventory enabled so that they can be ordered again.
     * The item quantity is changed to the measurement quantity, such that if
     * the item quantity is 2 and the item is 3 ft of fabric, the measurement
     * quantity will be 6
     *
     * @param array    $items array of item arrays
     * @param WC_Order $order the original order
     *
     * @return array the item
     * @since 3.0
     */
    public function order_again_item_set_quantity($items, $order)
    {
        if (isset($_GET['order_again'])) {
            foreach ($items as &$item) {
                // skip non-product line items like tax, etc
                if ('line_item' !== $item['type']) {
                    continue;
                }
                $product = \wc_get_product($item['product_id']);
                if (isset($item['item_meta']['_fq_measurement_data'][0]) && $item['item_meta']['_fq_measurement_data'][0] && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
                    $measurement_data = \maybe_unserialize($item['item_meta']['_fq_measurement_data'][0]);
                    $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
                    $total_measurement = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($measurement_data['_measurement_needed_unit'], $measurement_data['_measurement_needed']);
                    // save the item quantity for order_again_cart_item_data()
                    $item['item_meta']['_quantity'][0] = $item['qty'];
                    // save the unit quantity (ie item quantity is '2' pieces of fabric at 3 ft each, so the unit quantity is '6'
                    $item['qty'] *= $total_measurement->get_value($settings->get_pricing_unit());
                }
            }
        }
        return $items;
    }
    /** Admin methods ******************************************************/
    /**
     * Manage the order stock (whether restore or reduce) from the order admin
     * returning the true product stock change if this is for a pricing calculator
     * product/item with inventory enabled.  Ie 2 pieces of cloth at 3 ft each
     * we'd want to return 6
     *
     * @param int|float $quantity the new quantity
     * @param string    $item_id  the order item identifier
     *
     * @return int|float $quantity the measurement quantity
     * @since 3.0
     */
    public function admin_manage_order_stock($quantity, $item_id)
    {
        $order_id = \absint(\sanitize_text_field(\wp_unslash($_POST['order_id'])));
        $order = \wc_get_order($order_id);
        $order_items = $order->get_items();
        $product = \wc_get_product($order_items[$item_id]['product_id']);
        if (isset($order_items[$item_id]['measurement_data']) && \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
            $settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product);
            $measurement_data = \maybe_unserialize($order_items[$item_id]['measurement_data']);
            $total_amount = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($measurement_data['_measurement_needed_unit'], $measurement_data['_measurement_needed']);
            // this is a pricing calculator product so we want to return the
            // quantity in terms of units, ie 2 pieces of cloth at 3 ft each = 6
            $quantity *= $total_amount->get_value($settings->get_pricing_unit());
        }
        return $quantity;
    }
    /**
     * Returns the quantity input step for order items on admin order page.
     * This is needed when Calculate Inventory is enabled, because in case of a decimal
     * increment step, order can not be updated in the admin area.
     *
     * @param string|int
     * @param WC_Product $product
     * @return string|int
     */
    public function woocommerce_quantity_input_step_admin($step, $product)
    {
        if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::pricing_calculator_inventory_enabled($product)) {
            $settings = (new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($product))->get_settings();
            $step = !empty($settings['fq']['increment']) ? $settings['fq']['increment'] : $step;
        }
        return $step;
    }
}
