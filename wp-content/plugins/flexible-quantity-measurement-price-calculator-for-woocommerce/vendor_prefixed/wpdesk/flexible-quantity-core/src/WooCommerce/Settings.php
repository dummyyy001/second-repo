<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WC_Product;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Persistence\WooCommerceProductContainer;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units;
/**
 * Admin Settings API used by the price calculator plugin
 *
 * @since 2.0
 */
class Settings
{
    /**
     * Default area measurement unit
    */
    const DEFAULT_AREA = 'sq cm';
    /**
     * Default volume measurement unit
    */
    const DEFAULT_VOLUME = 'ml';
    const SETTINGS_META_KEY = 'flexible_quantity_settings';
    /**
     * @var WC_Product the product these settings are associated with (optional)
     */
    private $product;
    /**
     * @var array the raw settings array
     */
    private $settings;
    /**
     * @var array raw pricing rules array (if any)
     */
    private $pricing_rules;
    private $settings_container;
    /**
     * Construct and initialize the price calculator settings
     *
     * @param mixed $product Optional product or product id to load settings from.
     *                       Otherwise, default settings object is instantiated
     */
    public function __construct($product = null)
    {
        // product id
        if (\is_numeric($product)) {
            $product = \wc_get_product($product);
        }
        // have a product
        if ($product instanceof \WC_Product) {
            if ($product->is_type('variation')) {
                $product = \wc_get_product($product->get_parent_id());
            }
            $this->product = $product;
            $this->settings_container = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Persistence\WooCommerceProductContainer($product->get_id());
        }
    }
    /**
     * Returns the product associated with this settins object, if any
     *
     * @return WC_Product the product object
     */
    public function get_product()
    {
        return $this->product;
    }
    /**
     * Sets the underlying settings array
     *
     * @since  3.0
     * @param  array|string $settings array or serialized array of settings
     * @return array the raw settings
     */
    public function set_raw_settings($settings)
    {
        $settings = $this->settings_container->set(self::SETTINGS_META_KEY, $settings);
        return $settings;
    }
    /**
     * Returns the underlying settings array
     *
     * @return array the settings array
     */
    public function get_settings()
    {
        return $this->settings_container->has(self::SETTINGS_META_KEY) === \true ? $this->settings_container->get(self::SETTINGS_META_KEY) : [];
    }
    /**
     * Gets the configured calculator type (if any)
     *
     * @return string the calculator type, one of
     *         'dimension', 'area', 'area-dimension', 'area-linear', 'area-surface',
     *         'volume', 'volume-dimension', 'volume-area',
     *         'weight', 'wall-dimension' or ''
     */
    public function get_calculator_type()
    {
        $settings = $this->get_settings();
        $unit = isset($settings['fq']['unit']) ? $settings['fq']['unit'] : '';
        return \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_calculator_type($unit);
    }
    /**
     * Returns true if the calculator is a derived type (meaning more than one
     * measurement is supplied to derive a final amount), ie Area (LxW)
     *
     * @since  3.0
     * @return bool true if the calculator type is derived
     */
    public function is_calculator_type_derived()
    {
        return \in_array($this->get_calculator_type(), ['other', 'area-dimension', 'area-linear', 'area-surface', 'volume-dimension', 'volume-area', 'wall-dimension'], \true);
    }
    /**
     * Gets the measurements settings for the current calculator.  If a frontend
     * label is not set for a measurement, the unit will be used.  If the
     * returned measurements include more than one, for instance length, width or
     * area, height, a common unit will be available on all of them to faciliate
     * deriving a compound measurement (ie area or volume)
     *
     * @return Measurement[] Array of measurements.
     */
    public function is_decimals_enabled()
    {
        $settings = $this->get_settings();
        $decimals_enabled = isset($settings['fq']['decimals_enabled']) && $settings['fq']['decimals_enabled'] == 'yes';
        return $decimals_enabled && isset($settings['fq']['decimals']) && \is_array($settings['fq']['decimals']);
    }
    public function get_calculator_measurements()
    {
        $measurements = [];
        $settings = $this->get_settings();
        $common_unit = $settings['fq']['unit'];
        if ($this->is_decimals_enabled()) {
            foreach ($settings['fq']['decimals'] as $name => $value) {
                $type = $value['type'];
                $is_editable = $type === 'user';
                if ($is_editable) {
                    $val = \is_numeric($value[$type]['min_quantity']) ? \floatval($value[$type]['min_quantity']) : '';
                    if ($val == '') {
                        $val = \is_numeric($value[$type]['max_quantity']) ? \floatval($value[$type]['max_quantity']) : 1;
                    }
                } else {
                    $val = \is_numeric($value[$type]['size']) ? \floatval($value[$type]['size']) : 1;
                }
                $measurement = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($value[$type]['unit'], $val, $name, $value[$type]['label'], $is_editable ? 'yes' : 'no', $this->get_options($name));
                $measurement->set_common_unit($common_unit);
                $measurements[] = $measurement;
            }
            // }
        } else {
            $val = \is_numeric($settings['fq']['min_range']) ? \floatval(\is_numeric($settings['fq']['min_range'])) : '';
            if ($val == '') {
                $val = \is_numeric($settings['fq']['max_range']) ? \floatval($settings['fq']['max_range']) : 1;
            }
            $label = !empty($settings['fq']['label']) ? $settings['fq']['label'] : '';
            $measurement = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement($common_unit, $val, 'length', $label, 'yes', $this->get_options('dimension'));
            $measurement->set_common_unit($common_unit);
            $measurements[] = $measurement;
        }
        return $measurements;
    }
    /**
     * Returns true if the calculator is enabled
     *
     * @return bool true if the calculator is enabled, false otherwise
     */
    public function is_calculator_enabled()
    {
        $settings = $this->get_settings();
        return isset($settings['fq']['enable']) && $settings['fq']['enable'] === 'yes' && !$this->product->is_type('variable') && !$this->product->is_type('grouped');
    }
    /**
     * Returns true if "show product price per unit" is enabled
     *
     * @return bool true if the price per unit should be displayed on the frontend
     */
    public function is_pricing_enabled()
    {
        return $this->is_calculator_enabled();
    }
    /**
     * Returns true if the quantity calculator is enabled (this is normal mode
     * where the price of a product is not per unit, ie not $/sq ft)
     *
     * @since  3.0
     * @return bool true if the quantity calculator is enabled
     */
    public function is_quantity_calculator_enabled()
    {
        return $this->is_calculator_enabled() && !$this->is_pricing_calculator_enabled();
    }
    /**
     * Returns true if the calculator pricing per unit is enabled, meaning that
     * the product price is defined "per unit" (ie $/sq ft) and the customer
     * purchases a custom amount
     *
     * @since  3.0
     * @return bool true if calculator pricing is enabled
     */
    public function is_pricing_calculator_enabled()
    {
        return $this->is_pricing_enabled();
    }
    /**
     * Returns true if the calculator pricing inventory is enabled.  This means
     * that inventory is tracked "per foot" or whatever, rather than per item.
     *
     * @since  3.0
     * @return bool true if pricing and pricing inventory is enabled
     */
    public function is_pricing_inventory_enabled()
    {
        $settings = $this->get_settings();
        return $this->is_pricing_calculator_enabled() && isset($settings['fq']['calculate_inventory']) && 'yes' == $settings['fq']['calculate_inventory'];
    }
    /**
     * Returns true if the calculator pricing calculated weight is enabled.
     * This means that weight is calcualted "per foot" or whatever, rather
     * than per item.
     *
     * @since  3.0
     * @return bool true if pricing and calculated weight is enabled
     */
    public function is_pricing_calculated_weight_enabled()
    {
        $calculator_type = $this->get_calculator_type();
        return $this->is_pricing_calculator_enabled() && isset($this->settings[$calculator_type]['pricing']['weight']['enabled']) && 'yes' == $this->settings[$calculator_type]['pricing']['weight']['enabled'];
    }
    /**
     * Sets the given pricing rules, verifying for correctness: a rule must have
     * a numeric (non-negative) start and price to be valid.  The pricing rules
     * will be in terms of the pricing unit.
     *
     * @since 3.0
     * @param array $pricing_rules the pricing rules
     */
    private function set_pricing_rules($pricing_rules)
    {
        $this->pricing_rules = [];
        if (\is_array($pricing_rules)) {
            foreach ($pricing_rules as $rule) {
                $rule = (array) \apply_filters('fq_price_calculator_settings_rule', $rule, $this->product);
                if (isset($rule['range_start'], $rule['regular_price']) && \is_numeric($rule['range_start']) && $rule['range_start'] >= 0 && \is_numeric($rule['regular_price']) && $rule['regular_price'] >= 0) {
                    $this->pricing_rules[] = $rule;
                }
            }
        }
    }
    public function is_shipping_table_enabled()
    {
        $settings = $this->get_settings();
        return isset($settings['fq']['shipping_table']['enable']) && 'yes' === $settings['fq']['shipping_table']['enable'];
    }
    public function is_pricing_table_enabled()
    {
        $settings = $this->get_settings();
        return isset($settings['fq']['pricing_table']['enable']) && 'yes' === $settings['fq']['pricing_table']['enable'];
    }
    private function get_settings_pricing_rules()
    {
        $settings = $this->get_settings();
        $pricing_rules = [];
        $default_price = $this->get_basic_regular_price();
        if ($this->is_pricing_calculator_enabled()) {
            if ($this->is_pricing_table_enabled()) {
                foreach ($settings['fq']['pricing_table']['items']['from'] as $key => $value) {
                    $price = \trim(\str_replace(',', '.', $settings['fq']['pricing_table']['items']['price'][$key]));
                    $price = \is_numeric($price) ? \abs($price) : $default_price;
                    $sale_price = \trim(\str_replace(',', '.', $settings['fq']['pricing_table']['items']['sale_price'][$key]));
                    $sale_price = \is_numeric($sale_price) ? \abs($sale_price) : '';
                    $pricing_rules[] = ['range_start' => $settings['fq']['pricing_table']['items']['from'][$key], 'range_end' => $settings['fq']['pricing_table']['items']['to'][$key], 'price' => !empty($sale_price) ? $sale_price : $price, 'regular_price' => $price, 'sale_price' => $sale_price];
                }
            }
        }
        return $pricing_rules;
    }
    public function get_settings_shipping_rules()
    {
        $settings = $this->get_settings();
        $shipping_rules = [];
        if ($this->is_pricing_calculator_enabled()) {
            if ($this->is_shipping_table_enabled()) {
                foreach ($settings['fq']['shipping_table']['items']['from'] as $key => $value) {
                    $shipping_id = $settings['fq']['shipping_table']['items']['shipping_class'][$key];
                    $shipping_rules[] = ['range_start' => $settings['fq']['shipping_table']['items']['from'][$key], 'range_end' => $settings['fq']['shipping_table']['items']['to'][$key], 'shipping_id' => $shipping_id];
                }
            }
        }
        return $shipping_rules;
    }
    /**
     * Gets the pricing rules (if any) associated with this calculator.
     *
     * Pricing rules are available only if the pricing calculator is enabled.
     * Pricing rules ranges default to pricing units.
     *
     * @since 3.0
     *
     * @param  string|null $to_unit optional units to return the pricing rules ranges in, defaults to pricing units
     * @return array of pricing rules with ranges in terms of $to_unit
     */
    public function get_pricing_rules($to_unit = null) : array
    {
        // default if the pricing calculator is not enabled
        $pricing_rules = [];
        if ($this->product && $this->is_pricing_calculator_enabled()) {
            // load the pricing rules when needed
            if (null === $this->pricing_rules) {
                $rules = $this->get_settings_pricing_rules();
                if (\is_array($rules)) {
                    $this->set_pricing_rules($rules);
                }
            }
            // default pricing rules
            $pricing_rules = $this->pricing_rules;
            // if a conversion
            if (!empty($pricing_rules) && $to_unit && $to_unit !== $this->get_pricing_unit()) {
                foreach ($pricing_rules as &$rule) {
                    $rule['range_start'] = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement::convert($rule['range_start'], $this->get_pricing_unit(), $to_unit);
                    if ('' !== $rule['range_end']) {
                        $rule['range_end'] = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement::convert($rule['range_end'], $this->get_pricing_unit(), $to_unit);
                    }
                }
            }
        }
        return \is_array($pricing_rules) ? $pricing_rules : [];
    }
    /**
     * Determines if pricing rules are enabled for this calculator.
     *
     * @return bool
     * @since  3.0
     *
     * @see Settings::has_pricing_rules() alias
     */
    public function pricing_rules_enabled() : bool
    {
        return $this->has_pricing_rules();
    }
    /**
     * Determines whether there are pricing rules available for this calculator.
     *
     * @return bool
     * @since  3.0
     *
     * @see Settings::pricing_rules_enabled() alias
     */
    public function has_pricing_rules() : bool
    {
        return !empty($this->get_pricing_rules());
    }
    /**
     * Gets the price for the given $measurement, if there is a matching pricing
     * rule, or null
     *
     * @param Measurement $measurement the product total measurement
     *
     * @return float the price for the given $measurement (regular or sale)
     * @since  3.0
     */
    public function get_shipping_class_id(\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement $measurement)
    {
        // get the value in pricing units for comparison
        $measurement_value = $measurement->get_value($this->get_pricing_unit());
        foreach ($this->get_settings_shipping_rules() as $rule) {
            // if we find a matching rule, return the price
            if ($measurement_value >= $rule['range_start'] && ('' === $rule['range_end'] || $measurement_value <= $rule['range_end'])) {
                return $rule['shipping_id'];
            }
        }
        return $this->product->get_shipping_class_id();
    }
    public function get_pricing_rules_price(\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement $measurement)
    {
        // get the value in pricing units for comparison
        $measurement_value = $measurement->get_value($this->get_pricing_unit());
        foreach ($this->get_pricing_rules() as $rule) {
            // if we find a matching rule, return the price
            if ($measurement_value >= $rule['range_start'] && ('' === $rule['range_end'] || $measurement_value <= $rule['range_end'])) {
                return $rule['price'];
            }
        }
        return $this->product->get_price();
    }
    /**
     * Returns the true if there's a pricing table sale running
     *
     * @since  3.0
     * @return bool true if there's a pricing table sale running, false otherwise
     */
    public function pricing_rules_is_on_sale() : bool
    {
        foreach ($this->get_pricing_rules() as $rule) {
            if ('' !== $rule['sale_price']) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Returns the minimum possible pricing rule price, or null
     *
     * @since  3.0
     * @return int|float the minimum possible pricing rule price, or null
     */
    public function get_pricing_rules_minimum_price()
    {
        $min = null;
        foreach ($this->get_pricing_rules() as $rule) {
            if (null === $min) {
                $min = \PHP_INT_MAX;
                // initialize to the largest possible number
            }
            $min = \min($min, $rule['price']);
        }
        return $min;
    }
    /**
     * Returns the largest possible pricing rule price, or null
     *
     * @since  3.0
     * @return int|float the largest possible pricing rule price, or null
     */
    public function get_pricing_rules_maximum_price()
    {
        $max = null;
        foreach ($this->get_pricing_rules() as $rule) {
            if (null === $max) {
                $max = -1;
                // initialize to an impossible price
            }
            $max = \max($max, $rule['price']);
        }
        return $max;
    }
    /**
     * Returns the minimum possible pricing rule price, or null
     *
     * @since  3.0
     * @return int|float the minimum possible pricing rule regular price, or null
     */
    public function get_pricing_rules_minimum_regular_price()
    {
        $min = null;
        foreach ($this->get_pricing_rules() as $rule) {
            if (null === $min) {
                $min = \PHP_INT_MAX;
                // initialize to the largest possible number
            }
            $min = \min($min, $rule['regular_price']);
        }
        return $min;
    }
    /**
     * Returns the maximum possible pricing rule price, or null
     *
     * @since  3.4.0
     * @return int|float the minimum possible pricing rule regular price, or null
     */
    public function get_pricing_rules_maximum_regular_price()
    {
        $max = null;
        foreach ($this->get_pricing_rules() as $rule) {
            if (null === $max) {
                $max = -1;
                // initialize to an impossible price
            }
            $max = \max($max, $rule['regular_price']);
        }
        return $max;
    }
    /**
     * Returns the minimum possible pricing rule sale price, or null
     *
     * @since  3.8.1
     * @return float the minimum possible pricing rule regular price, or null
     */
    public function get_pricing_rules_minimum_sale_price()
    {
        $min = null;
        foreach ($this->get_pricing_rules() as $rule) {
            // skip rules with no sale price
            if ('' === $rule['sale_price']) {
                continue;
            }
            if (null === $min) {
                // initialize to the largest possible number
                $min = \PHP_INT_MAX;
            }
            $min = \min($min, $rule['sale_price']);
        }
        return $min;
    }
    /**
     * Returns the maximum possible pricing rule sale price, or null
     *
     * @since  3.8.1
     * @return float the minimum possible pricing rule regular price, or null
     */
    public function get_pricing_rules_maximum_sale_price()
    {
        $max = null;
        foreach ($this->get_pricing_rules() as $rule) {
            // skip rules with no sale price
            if ('' === $rule['sale_price']) {
                continue;
            }
            if (null === $max) {
                $max = -1;
                // initialize to an impossible price
            }
            $max = \max($max, $rule['sale_price']);
        }
        return $max;
    }
    /**
     * Returns the price html for the given pricing rule, ie:
     * * -$10 / ft- $5 / ft
     * * $5 / ft
     * * -$10 / ft- Free!
     * * Free!
     *
     * @since  3.0
     * @param  array $rule the pricing rule with keys:
     *                     'range_start', 'range_end',
     *                     'price', 'regular_price' and 'sale_price'
     * @return string pricing rule price html
     */
    public function get_pricing_rule_price_html($rule)
    {
        $price_html = '';
        $sep = \apply_filters('fq_price_calculator_pricing_label_separator', '/');
        if ($rule['price'] > 0) {
            if ('' !== $rule['sale_price'] && '' !== $rule['regular_price']) {
                $price_html .= \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::get_price_html_from_to($rule['regular_price'], $rule['price'], $sep . ' ' . \__($this->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
            } else {
                $price_html .= \wc_price($rule['price']) . ' ' . $sep . ' ' . \__($this->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce');
            }
        } elseif ('' === $rule['price']) {
            // no-op (for now)
        } elseif (0 == $rule['price']) {
            if ($rule['price'] === $rule['sale_price'] && '' !== $rule['regular_price']) {
                $price_html .= \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Product::get_price_html_from_to($rule['regular_price'], \__('Free!', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__($this->get_pricing_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
            } else {
                $price_html = \__('Free!', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
            }
        }
        return \apply_filters('fq_price_calculator_get_pricing_rule_price_html', $price_html, $rule, $this);
    }
    /**
     * Returns the calculator pricing unit, if this is a pricing calculator
     *
     * @return string pricing unit
     */
    public function get_pricing_unit()
    {
        if ($this->is_calculator_enabled()) {
            $settings = $this->get_settings();
            $unit = $settings['fq']['unit'];
            return $unit;
        }
        return '';
    }
    public function get_basic_regular_price()
    {
        return $this->product->get_price('edit');
    }
    /**
     * Returns the calculator pricing overage, if this is a pricing calculator.
     *
     * @since 3.12.0
     *
     * @return float pricing overage percentage
     */
    public function get_pricing_overage()
    {
        $overage = 0.0;
        if ($this->is_pricing_enabled()) {
            $calculator_type = $this->get_calculator_type();
            if (isset($this->settings[$calculator_type]['pricing']['overage']) && $this->settings[$calculator_type]['pricing']['overage']) {
                $overage = (float) $this->settings[$calculator_type]['pricing']['overage'] / 100;
            }
        }
        return $overage;
    }
    /**
     * Return the calculator input accepted type.
     *
     * @since 3.12.0
     *
     * @param  string $measurement_input
     * @return string
     */
    public function get_accepted_input($measurement_input)
    {
        return 'user';
    }
    /**
     * Return the calculator input accepted settings.
     *
     * @since 3.12.0
     *
     * @param  string $measurement_input
     * @return array
     */
    public function get_input_attributes($measurement_input)
    {
        $settings = $this->get_settings();
        if ($this->is_decimals_enabled()) {
            if (!isset($settings['fq']['decimals'][$measurement_input])) {
                return [];
            }
            $measurement_settings = $settings['fq']['decimals'][$measurement_input];
            if ('user' !== $measurement_settings['type']) {
                return [];
            }
            $min = $measurement_settings['user']['min_quantity'];
            $max = $measurement_settings['user']['max_quantity'];
            $step = $measurement_settings['user']['increment'] == '' ? 1 : $measurement_settings['user']['increment'];
        } else {
            $min = $settings['fq']['min_range'];
            $max = $settings['fq']['max_range'];
            $step = $settings['fq']['increment'] == '' ? 1 : $settings['fq']['increment'];
        }
        $args = ['min' => $min, 'max' => $max, 'step' => $step];
        return \array_filter(\wp_parse_args($args, ['min' => 0, 'max' => '', 'step' => '']));
    }
    /**
     * Returns an array of option values for the given measurement.  This is
     * used for the pricing calculator only.
     *
     * @since 3.0.0
     *
     * @param  string $measurement_name the measurement name
     * @return array associative array of measurement option values to label
     */
    public function get_options($measurement_name)
    {
        $calculator_type = $this->get_calculator_type();
        $options = [];
        if ($this->is_pricing_calculator_enabled() && isset($this->settings[$calculator_type][$measurement_name]['options']) && \is_array($this->settings[$calculator_type][$measurement_name]['options'])) {
            foreach ($this->settings[$calculator_type][$measurement_name]['options'] as $value) {
                if ('' !== $value) {
                    $result = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Measurement::convert_to_float($value);
                    $options[(string) $result] = $value;
                }
            }
        }
        return $options;
    }
    /**
     * Returns the calculator pricing label, if this is a pricing calculator.
     * This is the label that would appear next to the price, as in: $10 ft.
     *
     * @return string pricing label
     */
    public function get_pricing_label()
    {
        $pricing_label = '';
        $settings = $this->get_settings();
        if ($this->is_pricing_enabled()) {
            // default to the unit
            if (isset($settings['fq']['unit']) && $settings['fq']['unit']) {
                $all_units = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_all_units();
                $unit = isset($all_units[$settings['fq']['unit']]['label']) ? $all_units[$settings['fq']['unit']]['label'] : $settings['fq']['unit'];
                $pricing_label = \sprintf(\esc_html__('per %s', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $unit);
            }
        }
        return \apply_filters('fq_price_calculator_pricing_label', $pricing_label, $this);
    }
    /**
     * Returns a default settings array
     *
     * @return array default settings array
     */
    private function get_default_settings()
    {
        // get the system units so we provide a nice convenient default
        $default_dimension_unit = \get_option('woocommerce_dimension_unit');
        $default_area_unit = \get_option('woocommerce_area_unit');
        $default_volume_unit = \get_option('woocommerce_volume_unit');
        // see the doc block for this method as to yuno get_option() {BR 2017-04-12}
        $default_weight_unit = $this->get_woocommerce_weight_unit();
        $length = \esc_html__('Length', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_length = \esc_html__('Required Length', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $width = \esc_html__('Width', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_width = \esc_html__('Required Width', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $height = \esc_html__('Height', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_height = \esc_html__('Required Height', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $area = \esc_html__('Area', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_area = \esc_html__('Required Area', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_volume = \esc_html__('Required Volume', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $req_weight = \esc_html__('Required Weight', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $distance = \esc_html__('Distance around your room', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
        $settings = [
            'calculator_type' => '',
            'dimension' => ['pricing' => ['label' => '', 'unit' => $default_dimension_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $req_length, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'enabled' => 'yes', 'options' => []], 'width' => ['label' => $req_width, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'enabled' => 'no', 'options' => []], 'height' => ['label' => $req_height, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'enabled' => 'no', 'options' => []]],
            'area' => ['pricing' => ['label' => '', 'unit' => $default_area_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'area' => ['label' => $req_area, 'unit' => $default_area_unit, 'editable' => 'yes', 'options' => []]],
            'area-dimension' => ['pricing' => ['label' => '', 'unit' => $default_area_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $length, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'width' => ['label' => $width, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
            'area-linear' => ['pricing' => ['label' => '', 'unit' => $default_dimension_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $length, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'width' => ['label' => $width, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
            'area-surface' => ['pricing' => ['label' => '', 'unit' => $default_area_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $length, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'width' => ['label' => $width, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'height' => ['label' => $height, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
            'volume' => ['pricing' => ['label' => '', 'unit' => $default_volume_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'volume' => ['label' => $req_volume, 'unit' => $default_volume_unit, 'editable' => 'yes', 'options' => []]],
            'volume-dimension' => ['pricing' => ['label' => '', 'unit' => $default_volume_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $length, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'width' => ['label' => $width, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'height' => ['label' => $height, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
            'volume-area' => ['pricing' => ['label' => '', 'unit' => $default_volume_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'area' => ['label' => $area, 'unit' => $default_area_unit, 'editable' => 'yes', 'options' => []], 'height' => ['label' => $height, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
            'weight' => ['pricing' => ['label' => '', 'unit' => $default_weight_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'weight' => ['label' => $req_weight, 'unit' => $default_weight_unit, 'editable' => 'yes', 'options' => []]],
            // just a special case area calculator
            'wall-dimension' => ['pricing' => ['label' => '', 'unit' => $default_area_unit, 'enabled' => 'no', 'calculator' => ['enabled' => 'no'], 'inventory' => ['enabled' => 'no'], 'weight' => ['enabled' => 'no']], 'length' => ['label' => $distance, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []], 'width' => ['label' => $height, 'unit' => $default_dimension_unit, 'editable' => 'yes', 'options' => []]],
        ];
        return $settings;
    }
    /**
     * Return the WooCommerce weight unit. Copied from WP core get_option().
     *
     * We have to use a SQL query here to avoid filters, because of the way this class is instantiated --
     *  we use it for the calculator product and product page classes, some of which filter the weight option.
     *  As such, if we use get_option(), we get stuck in an infinite filter loop.
     * We can remove this and use the get_option() call when WC 3.0+ is required, as there are other ways to filter
     *  the weight unit at that point. {BR 2017-04-12}
     *
     * @since  3.11.3
     * @return string option value
     */
    protected function get_woocommerce_weight_unit()
    {
        global $wpdb;
        $row = $wpdb->get_row("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'woocommerce_weight_unit' LIMIT 1");
        // Has to be get_row instead of get_var because of funkiness with 0, false, null values
        if (\is_object($row)) {
            $value = $row->option_value;
        } else {
            // option does not exist; we shouldn't even get here, but if so, we can safely return the WC default in this case
            $value = 'kg';
        }
        return $value;
    }
    /**
     * Returns an array with all the measurement types
     *
     * @since  3.0
     * @return array of measurement type strings
     */
    public static function get_measurement_types()
    {
        return ['custom', 'other', 'dimension', 'area', 'area-dimension', 'area-linear', 'area-surface', 'volume', 'volume-dimension', 'volume-area', 'weight', 'wall-dimension'];
    }
    /**
     * Over time it's expected that the settings datastructure will change, the
     * purpose of this method is to safely ensure that the underlying settings
     * structure always represents the latest
     *
     * @since 3.0
     */
    private function update_settings()
    {
        if (\is_array($this->settings)) {
            // pricing 'inventory', weight and 'calculator' sub-settings were added in version 3.0
            foreach ($this->settings as $calculator_name => $calculator_settings) {
                if (\is_array($calculator_settings)) {
                    foreach ($calculator_settings as $setting_name => $values) {
                        if ('pricing' === $setting_name) {
                            if (!isset($this->settings[$calculator_name][$setting_name]['inventory'])) {
                                $this->settings[$calculator_name][$setting_name]['inventory'] = ['enabled' => 'no'];
                            }
                            if (!isset($this->settings[$calculator_name][$setting_name]['weight'])) {
                                $this->settings[$calculator_name][$setting_name]['weight'] = ['enabled' => 'no'];
                            }
                            if (!isset($this->settings[$calculator_name][$setting_name]['calculator'])) {
                                $this->settings[$calculator_name][$setting_name]['calculator'] = ['enabled' => 'no'];
                            }
                        }
                    }
                }
            }
            // measurement 'options' setting (defaults to array()) was added in version 3.0
            foreach ($this->settings as $calculator_name => $calculator_settings) {
                if (\is_array($calculator_settings)) {
                    foreach ($calculator_settings as $setting_name => $values) {
                        if ('pricing' !== $setting_name && !isset($this->settings[$calculator_name][$setting_name]['options'])) {
                            $this->settings[$calculator_name][$setting_name]['options'] = [];
                        }
                    }
                }
            }
        }
    }
    /**
     * Product input(s) cookie name.
     *
     * @since 3.12.0
     *
     * @return string
     */
    public function get_product_inputs_cookie_name()
    {
        return 'wc_price_calc_inputs_' . $this->product->get_id();
    }
}
