<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage;
class Units
{
    public static function unit_select($field_args = [])
    {
        $args = \wp_parse_args($field_args, ['show_label' => \true, 'show_warpper' => \true, 'label' => '', 'id' => 'select', 'name' => '', 'class' => '', 'value' => '', 'options' => [], 'desc_tip' => \false, 'description' => '']);
        $output = '';
        if ($args['show_warpper']) {
            $output .= '<p class="form-field ' . $args['id'] . '">';
        }
        if ($args['show_label']) {
            $output .= '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }
        if (\true === $args['desc_tip']) {
            $output .= \wc_help_tip($args['description']);
        }
        $output .= '<select id="' . $args['id'] . '" name="' . $args['name'] . '" class="' . $args['class'] . '">';
        foreach ($args['options'] as $option_key => $option_value) {
            if (\is_array($option_value)) {
                $output .= ' <optgroup data-group-id="' . $option_key . '" label="' . $option_value['label'] . '">';
                foreach ($option_value['units'] as $option_key2 => $option_value2) {
                    $selected = $option_key2 === $args['value'] ? 'selected' : '';
                    $output .= ' <option value="' . $option_key2 . '" ' . $selected . '>' . $option_value2['label'] . '</option>';
                }
                $output .= ' </optgroup>';
            }
        }
        $output .= ' </select>';
        if ($args['show_warpper']) {
            $output .= '</p>';
        }
        return $output;
    }
    /**
     * @return array
     */
    public static function get_all() : array
    {
        return ['other' => ['units' => self::other_units(), 'label' => \esc_html__('Other', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'weight' => ['units' => self::weight_units(), 'label' => \esc_html__('Weight', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'dimension' => ['units' => self::dimension_units(), 'label' => \esc_html__('Dimension', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'area' => ['units' => self::area_units(), 'label' => \esc_html__('Area', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'volume' => ['units' => self::volume_units(), 'label' => \esc_html__('Volume', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'volume-dimension' => ['units' => self::volume_dimensions_units(), 'label' => \esc_html__('Volume (LxWxH)', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'custom' => ['units' => self::custom_units(), 'label' => \esc_html__('Custom', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]];
    }
    public static function get_unit_label(string $key) : string
    {
        $all_units = self::get_all_units();
        if (isset($all_units[$key]['label'])) {
            return $all_units[$key]['label'];
        }
        return $key;
    }
    public static function get_all_units() : array
    {
        return \array_merge(self::other_units(), self::weight_units(), self::dimension_units(), self::area_units(), self::volume_units(), self::volume_dimensions_units());
    }
    public static function get_calculator_type($unit)
    {
        foreach (self::get_all() as $key => $value) {
            if (isset($value['units'])) {
                if (\array_key_exists($unit, $value['units'])) {
                    return $key;
                }
            }
        }
        return '';
    }
    public static function get_unit_options($unit)
    {
        foreach (self::get_all() as $key => $value) {
            if (isset($value['units'])) {
                if (\array_key_exists($unit, $value['units'])) {
                    return self::convert_units_to_simple_array($value['units'][$unit]['options']);
                }
            }
        }
        return [];
    }
    public static function convert_units_to_simple_array(array $units)
    {
        $result = [];
        foreach (self::get_all() as $val) {
            if (isset($val['units']) && \is_array($val['units'])) {
                foreach ($units as $unit) {
                    if (\array_key_exists($unit, $val['units'])) {
                        $result[$unit] = $val['units'][$unit]['label'];
                    }
                }
            }
        }
        return $result;
    }
    public static function other_units() : array
    {
        return (array) \apply_filters('fcm/other_units', ['item' => ['label' => \__('item', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['item']]]);
    }
    public static function custom_units() : array
    {
        return (array) \apply_filters('fcm/custom_units', self::get_custom_units());
    }
    private static function get_custom_units()
    {
        $result = [];
        $custom_units = \get_option(\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage::FQ_OPTION_NAME, []);
        if (empty($custom_units)) {
            return $result;
        }
        foreach ($custom_units as $unit) {
            $unit_name = $unit[\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage::FORM_UNIT_NAME];
            $result[$unit_name] = ['label' => \__($unit_name, 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => [$unit_name]];
        }
        return $result;
    }
    /**
     * Returns all available weight units
     *
     * @return array of weight units
     * @since 1.0.0
     */
    public static function weight_units() : array
    {
        return (array) \apply_filters('fcm/weight_units', ['g' => ['label' => \__('g', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['g']], 'kg' => ['label' => \__('kg', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['g', 'kg']], 't' => ['label' => \__('t', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['g', 'kg', 't']], 'oz' => ['label' => \__('oz', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['oz']], 'lbs' => ['label' => \__('lbs', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['oz', 'lbs']], 'tn' => ['label' => \__('tn', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['oz', 'lbs', 'tn']]]);
    }
    /**
     * Returns all available dimension units
     *
     * @return array of dimension units
     * @since 1.0.0
     */
    public static function dimension_units() : array
    {
        return (array) \apply_filters('fcm/dimension_units', ['mm' => ['label' => \__('mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm']], 'cm' => ['label' => \__('cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm']], 'm' => ['label' => \__('m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm', 'm']], 'km' => ['label' => \__('km', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm', 'm', 'km']], 'in' => ['label' => \__('in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in']], 'ft' => ['label' => \__('ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft']], 'yd' => ['label' => \__('yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft', 'yd']], 'mi' => ['label' => \__('mi', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft', 'yd', 'mi']]]);
    }
    /**
     * Returns all available area units
     *
     * @return array of area units
     * @since 1.0.0
     */
    public static function area_units() : array
    {
        return (array) \apply_filters('fcm/area_units', ['sq mm' => ['label' => \__('sq mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm']], 'sq cm' => ['label' => \__('sq cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm']], 'sq m' => ['label' => \__('sq m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm', 'm']], 'ha' => ['label' => \__('ha', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['mm', 'cm', 'm']], 'sq km' => ['label' => \__('sq km', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['m', 'km']], 'sq. in.' => ['label' => \__('sq in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in']], 'sq. ft.' => ['label' => \__('sq ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft']], 'sq. yd.' => ['label' => \__('sq yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft', 'yd']], 'acs' => ['label' => \__('acs', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['ft', 'yd']], 'sq. mi.' => ['label' => \__('sq mi', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in', 'ft', 'yd', 'mi']]]);
    }
    /**
     * Returns all available volume units
     *
     * @return array of volume units
     * @since 1.0.0
     */
    public static function volume_units() : array
    {
        return (array) \apply_filters('fcm/volume_units', ['ml' => ['label' => \__('ml', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['ml']], 'l' => ['label' => \__('l', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['ml', 'l']], 'cup' => ['label' => \__('cup', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['cup']], 'pt' => ['label' => \__('pt', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['pt']], 'qt' => ['label' => \__('qt', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['qt']], 'gal' => ['label' => \__('gal', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['gal']], 'fl. oz.' => ['label' => \__('fl oz', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['fl. oz']]]);
    }
    public static function volume_dimensions_units() : array
    {
        return (array) \apply_filters('fcm/volume_dimensions_units', ['cu cm' => ['label' => \__('cu cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['cm']], 'cu m' => ['label' => \__('cu m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['cm', 'm']], 'cu. in.' => ['label' => \__('cu in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['in']], 'cu. ft.' => ['label' => \__('cu ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['ft']], 'cu. yd.' => ['label' => \__('cu yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => ['yd']]]);
    }
}
