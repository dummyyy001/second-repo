<?php

namespace WDFQVendorFree;

\defined('ABSPATH') or exit;
use WDFQVendorFree\SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\WooCompatibility;
require_once __DIR__ . '/writepanel-product_data-calculator.php';
/**
 * Gets the WooCommerce product settings, containing measurement units.
 *
 * @since 3.3
 *
 * @return array
 */
function fq_price_calculator_get_wc_settings()
{
    $plugin_path = \wc()->plugin_path();
    if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\WooCompatibility::is_wc_version_gte('3.3')) {
        if (!\class_exists('WC_Settings_Page', \false) || !\class_exists('WC_Settings_Products', \false)) {
            if (!\class_exists('WC_Admin_Settings', \false)) {
                include_once $plugin_path . '/includes/admin/class-wc-admin-settings.php';
            }
            \WC_Admin_Settings::get_settings_pages();
        }
        $settings_products = new \WC_Settings_Products();
    } else {
        include_once $plugin_path . '/includes/admin/settings/class-wc-settings-page.php';
        $settings_products = (include $plugin_path . '/includes/admin/settings/class-wc-settings-products.php');
    }
    return $settings_products->get_settings();
}
/**
 * Returns all available weight units
 *
 * @since 3.0
 * @return array of weight units
 */
function fq_price_calculator_get_weight_units()
{
    $settings = \WDFQVendorFree\fq_price_calculator_get_wc_settings();
    foreach ($settings as $setting) {
        if ('woocommerce_weight_unit' === $setting['id']) {
            return $setting['options'];
        }
    }
    // default in case the woocommerce settings are not available
    return [\__('g', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('g', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('kg', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('kg', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('t', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('t', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('oz', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('oz', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('lbs', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('lbs', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('tn', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('tn', 'flexible-quantity-measurement-price-calculator-for-woocommerce')];
}
/**
 * Returns all available dimension units
 *
 * @since 3.0
 * @return array of dimension units
 */
function fq_price_calculator_get_dimension_units()
{
    $settings = \WDFQVendorFree\fq_price_calculator_get_wc_settings();
    if ($settings) {
        foreach ($settings as $setting) {
            if ('woocommerce_dimension_unit' === $setting['id']) {
                return $setting['options'];
            }
        }
    }
    // default in case the woocommerce settings are not available
    return [\__('mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('m', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('km', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('km', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('in', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('mi', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('mi', 'flexible-quantity-measurement-price-calculator-for-woocommerce')];
}
/**
 * Returns all available area units
 *
 * @since 3.0
 * @return array of area units
 */
function fq_price_calculator_get_area_units()
{
    $settings = \WDFQVendorFree\fq_price_calculator_get_wc_settings();
    if ($settings) {
        foreach ($settings as $setting) {
            if ('woocommerce_area_unit' === $setting['id']) {
                return $setting['options'];
            }
        }
    }
    // default in case the woocommerce settings are not available
    return [\__('sq mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq mm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq cm', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq m', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('ha', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('ha', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq km', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq km', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq. in.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq. ft.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq. yd.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('acs', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('acs', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__('sq. mi.', 'flexible-quantity-measurement-price-calculator-for-woocommerce') => \__('sq mi', 'flexible-quantity-measurement-price-calculator-for-woocommerce')];
}
/**
 * Returns all available volume units
 *
 * @since 3.0
 * @return array of volume units
 */
function fq_price_calculator_get_volume_units()
{
    $settings = \WDFQVendorFree\fq_price_calculator_get_wc_settings();
    if ($settings) {
        foreach ($settings as $setting) {
            if ('woocommerce_volume_unit' === $setting['id']) {
                return $setting['options'];
            }
        }
    }
    // default in case the woocommerce settings are not available
    return ['ml' => \__('ml', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'l' => \__('l', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'cu m' => \__('cu m', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'cup' => \__('cup', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'pt' => \__('pt', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'qt' => \__('qt', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'gal' => \__('gal', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'fl. oz.' => \__('fl oz', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'cu. in.' => \__('cu in', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'cu. ft.' => \__('cu ft', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'cu. yd.' => \__('cu yd', 'flexible-quantity-measurement-price-calculator-for-woocommerce')];
}
/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field with required fields 'id' and 'rbvalue'
 * @return void
 */
function fq_price_calculator_wp_radio($field)
{
    global $thepostid, $post;
    if (!$thepostid) {
        $thepostid = $post->ID;
    }
    if (!isset($field['class'])) {
        $field['class'] = 'radio';
    }
    if (!isset($field['wrapper_class'])) {
        $field['wrapper_class'] = '';
    }
    if (!isset($field['name'])) {
        $field['name'] = $field['id'];
    }
    if (!isset($field['value'])) {
        $product = \wc_get_product($thepostid);
        $field['value'] = $product ? $product->get_meta($field['name']) : '';
    }
    echo '<p class="form-field ' . \esc_attr($field['id']) . '_field ' . \esc_attr($field['wrapper_class']) . '"><label for="' . \esc_attr($field['id']) . '">' . \esc_html($field['label']) . '</label><input type="radio" class="' . \esc_attr($field['class']) . '" name="' . \esc_attr($field['name']) . '" id="' . \esc_attr($field['id']) . '" value="' . \esc_attr($field['rbvalue']) . '" ';
    \checked($field['value'], $field['rbvalue']);
    echo ' /> ';
    if (isset($field['description']) && $field['description']) {
        echo \wp_kses_post('<span class="description">' . $field['description'] . '</span>');
    }
    echo '</p>';
}
/**
 * Render pricing overage input based on the measurement calculator option.
 *
 * @since 3.12.0
 *
 * @param string $measurement_type
 * @param array $settings
 * @return void
 */
function fq_price_calculator_overage_input($measurement_type, $settings)
{
    $id = "_measurement_{$measurement_type}_pricing_overage";
    $value = isset($settings[$measurement_type]['pricing']['overage']) ? $settings[$measurement_type]['pricing']['overage'] : '';
    \woocommerce_wp_text_input(['id' => $id, 'value' => $value, 'type' => 'number', 'decimal' => 'decimal', 'class' => 'short small-text _measurement_pricing_overage', 'wrapper_class' => '_measurement_pricing_calculator_fields', 'placeholder' => '%', 'label' => \__('Add Overage ', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'description' => \__('If you need to add and charge for a cut or overage estimate in addition to the customer input, enter the percentage of the total measurement to use.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'custom_attributes' => ['min' => '0', 'max' => '100', 'step' => '1']]);
}
/**
 * Render attributes inputs based on the measurement calculator option.
 *
 * @since 3.12.0
 *
 * @param array $args
 * @return void
 */
function fq_price_calculator_attributes_inputs($args)
{
    $args = \wp_parse_args($args, ['measurement' => '', 'input_name' => '', 'input_label' => '', 'settings' => [], 'limited_field' => '']);
    $settings = $args['settings'];
    $measurement = $args['measurement'];
    $input_name = $args['input_name'];
    if (!isset($settings[$measurement]) || !isset($settings[$measurement][$input_name])) {
        return;
    }
    $inputs_id_prefix = $measurement === $input_name ? "_measurement_{$measurement}" : "_measurement_{$measurement}_{$input_name}";
    // for backwards compat to set an initial value; remove empty strings
    $original_options = \array_filter($settings[$measurement][$input_name]['options']);
    \woocommerce_wp_select(['id' => "{$inputs_id_prefix}_accepted_input", 'value' => isset($settings[$measurement][$input_name]['accepted_input']) ? $settings[$measurement][$input_name]['accepted_input'] : (!empty($original_options) ? 'limited' : 'free'), 'class' => 'short small-text _measurement_accepted_input', 'wrapper_class' => '_measurement_pricing_calculator_fields', 'label' => \sprintf(\__('%s Input', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $args['input_label']), 'options' => ['free' => \__('Accept free-form customer input', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'limited' => \__('Accept a limited set of customer inputs', 'flexible-quantity-measurement-price-calculator-for-woocommerce')], 'custom_attributes' => ['data-free' => ".{$inputs_id_prefix}_input_attributes_field", 'data-limited' => ".{$args['limited_field']}_field"]]);
    // these won't be set for stores upgrading to 3.12.0, have a sanity check
    $min_value = isset($settings[$measurement][$input_name]['input_attributes']['min']) ? $settings[$measurement][$input_name]['input_attributes']['min'] : '';
    $max_value = isset($settings[$measurement][$input_name]['input_attributes']['max']) ? $settings[$measurement][$input_name]['input_attributes']['max'] : '';
    $step_value = isset($settings[$measurement][$input_name]['input_attributes']['step']) ? $settings[$measurement][$input_name]['input_attributes']['step'] : '';
    ?>
	<p class="form-field <?php 
    echo \esc_attr($inputs_id_prefix);
    ?>_input_attributes_field _measurement_pricing_calculator_fields _measurement_input_attributes dimensions_field">
		<label><?php 
    echo \wp_kses_post(\sprintf(\__('%s Options', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $args['input_label']));
    ?></label>
		<span class="wrap">
		<input placeholder="<?php 
    \esc_attr_e('Min value', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?>"
				class="input-text wc_input_decimal" size="6" type="number" step="any"
				name="<?php 
    echo \esc_attr($inputs_id_prefix);
    ?>_input_attributes[min]"
				value="<?php 
    echo \esc_attr($min_value);
    ?>"/>
		<input placeholder="<?php 
    \esc_attr_e('Max value', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?>"
				class="input-text wc_input_decimal" size="6" type="number" step="any"
				name="<?php 
    echo \esc_attr($inputs_id_prefix);
    ?>_input_attributes[max]"
				value="<?php 
    echo \esc_attr($max_value);
    ?>"/>
		<input placeholder="<?php 
    \esc_attr_e('Increment', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?>"
				class="input-text wc_input_decimal last" size="6" type="number" step="any"
				name="<?php 
    echo \esc_attr($inputs_id_prefix);
    ?>_input_attributes[step]"
				value="<?php 
    echo \esc_attr($step_value);
    ?>" />
		</span>
		<?php 
    echo \wc_help_tip(\esc_html__('If applicable, enter limits to restrict customer input, such as an accepted increment and/or maximum value.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
    //phpcs:ignore
    ?>
	</p>
	<?php 
}
