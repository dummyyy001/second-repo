<?php

namespace WDFQVendorFree;

\defined('ABSPATH') or exit;
use WDFQVendorFree\Helper\Unit;
use WDFQVendorFree\SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings;
\add_action('woocommerce_product_write_panel_tabs', __NAMESPACE__ . '\\fq_price_calculator_product_rates_panel_tab', 99);
\add_action('woocommerce_product_data_panels', __NAMESPACE__ . '\\fq_price_calculator_product_rates_panel_content');
\add_action('wp_ajax_' . \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel::FQ_AJAX_ACTION, __NAMESPACE__ . '\\fq_generate_decimals_template_from_ajax');
// If called from admin panel
\add_action('wp_ajax_nopriv_' . \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel::FQ_AJAX_ACTION, __NAMESPACE__ . '\\fq_generate_decimals_template_from_ajax');
// If called from front end
/**
 * Adds the "Calculator" tab to the Product Data postbox in the admin product interface
 */
function fq_price_calculator_product_rates_panel_tab()
{
    echo \wp_kses_post('<li class="measurement_tab hide_if_grouped hide_if_variable"><a href="#measurement_product_data"><span>' . \_x('Flexible Quantity', 'product_menu', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . '</span></a></li>');
}
function fq_generate_decimals_template_parts($size_type, $size_label, $settings, $units)
{
    ?>
	<div class="fq-field-type fq-field-<?php 
    echo \esc_attr($size_type);
    ?>">
	<?php 
    \woocommerce_wp_select(['id' => 'fq_dec_' . $size_type . '_type', 'name' => 'fq[decimals][' . $size_type . '][type]', 'value' => isset($settings['fq']['decimals'][$size_type]['type']) ? $settings['fq']['decimals'][$size_type]['type'] : 'fixed', 'placeholder' => '', 'class' => 'wqm-select fq_field_type_selector fq_dec_' . $size_type, 'label' => $size_label, 'options' => ['fixed' => \esc_html__('Fixed value', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'user' => \esc_html__('User value', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]]);
    // esc_html__( 'Length', 'flexible-quantity-measurement-price-calculator-for-woocommerce' )
    ?>

	<div class="flex-row-container decimals-fields-with-labels fixed-fields">
		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_fixed_' . $size_type . '_label', 'name' => 'fq[decimals][' . $size_type . '][fixed][label]', 'value' => isset($settings['fq']['decimals'][$size_type]['fixed']['label']) ? $settings['fq']['decimals'][$size_type]['fixed']['label'] : '', 'class' => 'fq_dec_fixed_' . $size_type . '_label', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Field label', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Enter the name for the dimension field label.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_select(['id' => 'fq_dec_fixed_' . $size_type . '_unit', 'name' => 'fq[decimals][' . $size_type . '][fixed][unit]', 'value' => isset($settings['fq']['decimals'][$size_type]['fixed']['unit']) ? $settings['fq']['decimals'][$size_type]['fixed']['unit'] : '', 'class' => 'wqm-select fq_dec_fixed_' . $size_type . '_unit', 'label' => \esc_html__('Unit of measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => $units, 'desc_tip' => \true, 'description' => \esc_html__('Choose the unit of measure for the dimension.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_fixed_' . $size_type . '_size', 'name' => 'fq[decimals][' . $size_type . '][fixed][size]', 'value' => isset($settings['fq']['decimals'][$size_type]['fixed']['size']) ? $settings['fq']['decimals'][$size_type]['fixed']['size'] : '', 'class' => 'fq_dec_fixed_' . $size_type . '_size', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Value', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0'], 'desc_tip' => \true, 'description' => \esc_html__('Select the products size.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>
	</div>

	<div class="flex-row-container decimals-fields-with-labels user-fields">
		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_user_' . $size_type . '_label', 'name' => 'fq[decimals][' . $size_type . '][user][label]', 'value' => isset($settings['fq']['decimals'][$size_type]['user']['label']) ? $settings['fq']['decimals'][$size_type]['user']['label'] : '', 'class' => 'fq_dec_user_' . $size_type . '_label', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Field label', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'placeholder' => \esc_html__('Field label', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Enter the name for the dimension field label.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_select(['id' => 'fq_dec_user_' . $size_type . '_unit', 'name' => 'fq[decimals][' . $size_type . '][user][unit]', 'class' => 'wqm-select fq_dec_user_' . $size_type . '_unit', 'value' => isset($settings['fq']['decimals'][$size_type]['user']['unit']) ? $settings['fq']['decimals'][$size_type]['user']['unit'] : '', 'label' => \esc_html__('Unit of measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => $units, 'desc_tip' => \true, 'description' => \esc_html__('Choose the unit of measure for the dimension.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
			&nbsp;
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_user_' . $size_type . '_increment', 'name' => 'fq[decimals][' . $size_type . '][user][increment]', 'value' => isset($settings['fq']['decimals'][$size_type]['user']['increment']) ? $settings['fq']['decimals'][$size_type]['user']['increment'] : '', 'class' => 'fq_dec_user_' . $size_type . '_increment', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Increment', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0'], 'desc_tip' => \true, 'description' => \esc_html__('Fill in the increment value for the unit dimension.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_user_' . $size_type . '_min_quantity', 'name' => 'fq[decimals][' . $size_type . '][user][min_quantity]', 'value' => isset($settings['fq']['decimals'][$size_type]['user']['min_quantity']) ? $settings['fq']['decimals'][$size_type]['user']['min_quantity'] : '', 'class' => 'fq_dec_user_' . $size_type . '_min_quantity', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Minimum value', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0'], 'desc_tip' => \true, 'description' => \esc_html__('Set the minimum value for the unit dimension.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_text_input(['id' => 'fq_dec_user_' . $size_type . '_max_quantity', 'name' => 'fq[decimals][' . $size_type . '][user][max_quantity]', 'value' => isset($settings['fq']['decimals'][$size_type]['user']['max_quantity']) ? $settings['fq']['decimals'][$size_type]['user']['max_quantity'] : '', 'class' => 'fq_dec_user_' . $size_type . '_max_quantity', 'wrapper_class' => 'myclass', 'label' => \esc_html__('Maximum value', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0'], 'desc_tip' => \true, 'description' => \esc_html__('Set the maximum value for the unit dimension.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>
	</div>
	</div>
	<?php 
}
function fq_generate_decimals_template($settings, $unit = '')
{
    $weight = ['weight'];
    $length = ['dimension', 'area', 'volume-dimension'];
    $width = ['area', 'volume-dimension'];
    $height = ['volume-dimension'];
    $volume = ['volume'];
    $other = ['other'];
    $default_unit = isset($settings['fq']['unit']) ? $settings['fq']['unit'] : '';
    $unit = !empty($unit) ? $unit : $default_unit;
    $dimension = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_calculator_type($unit);
    $units = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_unit_options($unit);
    \ob_start();
    if (\in_array($dimension, $other)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('other', \esc_html__('Other', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    if (\in_array($dimension, $weight)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('weight', \esc_html__('Weight', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    if (\in_array($dimension, $volume)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('volume', \esc_html__('Volume', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    if (\in_array($dimension, $length)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('length', \esc_html__('Length', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    if (\in_array($dimension, $width)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('width', \esc_html__('Width', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    if (\in_array($dimension, $height)) {
        \WDFQVendorFree\fq_generate_decimals_template_parts('height', \esc_html__('Height', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $settings, $units);
    }
    $content = \ob_get_contents();
    \ob_clean();
    return $content;
}
function fq_generate_decimals_template_from_ajax()
{
    $result = ['result' => \false, 'content' => \__('Ups. something goes wrong with your ajax request. Please try again.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')];
    $settings = [];
    if (isset($_POST['nonce']) && isset($_POST['product_id']) && \wp_verify_nonce(\wc_clean(\wp_unslash($_POST['nonce'])), \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel::FQ_AJAX_ADMIN_NONCE)) {
        //phpcs:ignore
        if (!empty($_POST['product_id'])) {
            $product_id = \wc_clean(\wp_unslash($_POST['product_id']));
            $settings = (new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings((int) $product_id))->get_settings();
        }
        $unit = \wc_clean(\wp_unslash(isset($_POST['unit']) ? $_POST['unit'] : ''));
        $content = \WDFQVendorFree\fq_generate_decimals_template($settings, $unit);
        $result = ['result' => \true, 'content' => $content];
    }
    \wp_send_json($result);
}
/**
 * Adds the Calculator tab panel to the Product Data postbox in the product interface
 */
function fq_price_calculator_product_rates_panel_content()
{
    global $post;
    $measurement_units = ['weight' => \WDFQVendorFree\fq_price_calculator_get_weight_units(), 'dimension' => \WDFQVendorFree\fq_price_calculator_get_dimension_units(), 'area' => \WDFQVendorFree\fq_price_calculator_get_area_units(), 'volume' => \WDFQVendorFree\fq_price_calculator_get_volume_units()];
    $container = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($post->ID);
    $settings = $container->get_settings();
    $pricing_weight_wrapper_class = '';
    if ('no' === \get_option('woocommerce_enable_weight', \true)) {
        $pricing_weight_wrapper_class = 'hidden';
    }
    ?>
	<div id="measurement_product_data" class="panel woocommerce_options_panel">
		<div class="measurement-header">
			<h3><?php 
    \esc_html_e('Measurement calculator', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?></h3>
			<p class="measurement-header-descr">
				<?php 
    echo \wp_kses_post(\__('Enable the new unit of measurement for the product. Then enter the increment of the unit, its regular, and sale price. Also, specify the minimum and maximum quantity. You can also use the extended options, which will allow you to calculate prices based on the dimensions directly on the product page. <a href="https://wpde.sk/flexible-quantity-core-main" target="_blank">Check plugin\'s docs →</a>.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
    ?>
			</p>
		</div>
		<div class="product-panel-item">
		<div class="fcm-panel-item-body fcm-body-decimal">
			<div class="fq-field-type">            
			<div class="flex-row-container decimals-fields-with-labels">

				<div class="flex-row-item">
					<?php 
    \woocommerce_wp_checkbox(['id' => 'fq_enable', 'name' => 'fq[enable]', 'value' => isset($settings['fq']['enable']) ? $settings['fq']['enable'] : '', 'class' => 'fq_enable', 'label' => \esc_html__('New Unit of Measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'description' => \esc_html__('Enable the settings for the new unit of measurement.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
				</div>
			</div>
			</div>
		</div>
		</div>
		<div id="calculator-settings" class="product-panel-item fq-hidden-panels fcm-dimensions-decimals-panel">
		<div class="fcm-panel-item-body fcm-body-decimal">
			<div class="fq-field-type">
			<div class="flex-row-container decimals-fields-with-labels">
				<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_label', 'name' => 'fq[label]', 'value' => isset($settings['fq']['label']) ? $settings['fq']['label'] : '', 'placeholder' => '', 'class' => 'wqm-select fq_label', 'label' => \esc_html__('Unit Field Label', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Enter the name for the unit field label.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'text']);
    ?>
				</div>

				<div class="flex-row-item">
					<?php 
    echo \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::unit_select(
        //phpcs:ignore
        ['id' => 'fq_unit', 'name' => 'fq[unit]', 'value' => isset($settings['fq']['unit']) ? $settings['fq']['unit'] : '', 'placeholder' => '', 'class' => 'wqm-select fq_unit', 'label' => \esc_html__('Unit of measure', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'options' => \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Units::get_all(), 'desc_tip' => \true, 'description' => \esc_html__('Choose the new unit of measure for the product.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]
    );
    ?>
				</div>
				<div class="flex-row-item"></div>
			</div>
			</div>
			<div class="fq-field-type">

			<div class="flex-row-container decimals-fields-with-labels">                
			<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_price', 'name' => 'fq[price]', 'value' => isset($settings['fq']['price']) ? $settings['fq']['price'] : '', 'placeholder' => '', 'class' => 'fq_price', 'label' => \sprintf(\esc_html__('Price (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \get_woocommerce_currency_symbol()), 'desc_tip' => \true, 'description' => \esc_html__('Set the regular price for the unit of measure.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
				</div>
				<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_sale_price', 'name' => 'fq[sale_price]', 'value' => isset($settings['fq']['sale_price']) ? $settings['fq']['sale_price'] : '', 'placeholder' => '', 'class' => 'fq_sale_price', 'label' => \sprintf(\esc_html__('Sale price (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \get_woocommerce_currency_symbol()), 'desc_tip' => \true, 'description' => \esc_html__('Set the sale price for the unit of measure.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
				</div>
				<div class="flex-row-item"></div>     
			</div>

			<div class="flex-row-container decimals-fields-with-labels">

			<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_increment', 'name' => 'fq[increment]', 'value' => isset($settings['fq']['increment']) ? $settings['fq']['increment'] : '', 'placeholder' => '', 'class' => 'wqm-select fq_increment', 'label' => \esc_html__('Increment', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Fill in the increment value for the unit.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
				</div>

				<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_min_range', 'name' => 'fq[min_range]', 'value' => isset($settings['fq']['min_range']) ? $settings['fq']['min_range'] : '', 'placeholder' => '', 'class' => 'fq_min_range', 'label' => \esc_html__('Minimum quantity', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Set the minimum quantity for the unit.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
				</div>

				<div class="flex-row-item">
					<?php 
    \woocommerce_wp_text_input(['id' => 'fq_max_range', 'name' => 'fq[max_range]', 'value' => isset($settings['fq']['max_range']) ? $settings['fq']['max_range'] : '', 'placeholder' => '', 'class' => 'fq_max_range', 'label' => \esc_html__('Maximum quantity', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'desc_tip' => \true, 'description' => \esc_html__('Set the maximum quantity for the unit.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
				</div>
				<div class="flex-row-item">&nbsp;</div>
			</div>
			
	<div class="flex-row-container decimals-fields-with-labels">

		<div class="flex-row-item">
	<?php 
    \woocommerce_wp_checkbox(['id' => 'fq_calculate_inventory', 'name' => 'fq[calculate_inventory]', 'value' => isset($settings['fq']['calculate_inventory']) ? $settings['fq']['calculate_inventory'] : '', 'class' => 'fq_calcluate_inventory', 'label' => \esc_html__('Calculate Inventory', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'description' => \__('Check this box to define inventory per unit and calculate inventory based on the product. <br><br> If you select this option, then, e.g., 30 kg will be seen as 30 pcs. of the product. If you don\'t, then regardless of the purchased amount of the new unit of measurement, the stock will decrease by 1.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
		</div>
</div>
<div class="flex-row-container decimals-fields-with-labels">

<div class="flex-row-item">
	<?php 
    \woocommerce_wp_checkbox(['id' => 'fq_sold_individually', 'name' => 'fq[sold_individually]', 'value' => isset($settings['fq']['sold_individually']) ? $settings['fq']['sold_individually'] : '', 'class' => 'fq_sold_individually', 'label' => \esc_html__('Sold individually', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'description' => \__('Enable this to only allow one of this item to be bought in a single order. <br><br> With this option, the buyer will be able to order only 1 item of the product with the new unit of measure (e.g., 5 kg.). If you don’t select that option, it will be possible to buy more pcs. of the product (e.g., 5 kg. each).', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]);
    ?>
</div>
</div>
			</div>
		</div>
		</div>

	<?php 
    include __DIR__ . '/panel-part/dimensions-decimals.php';
    include __DIR__ . '/panel-part/pricing-table.php';
    include __DIR__ . '/panel-part/shipping-table.php';
    ?>

	</div>
	<?php 
}
// Hooked after the WC core handler
\add_action('woocommerce_process_product_meta', __NAMESPACE__ . '\\fq_price_calculator_process_product_meta_measurement', 10, 2);
/**
 * Save the custom fields
 *
 * @param int   $post_id post identifier
 * @param array $post    the post object
 */
function fq_price_calculator_process_product_meta_measurement($post_id, $post)
{
    if (isset($_POST['fq']) && \is_array($_POST['fq'])) {
        $settings_data = ['fq' => \wc_clean($_POST['fq'])];
        $container = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings($post_id);
        $container->set_raw_settings($settings_data);
        $settings = $container->get_settings();
        if (isset($settings['fq']['enable']) && $settings['fq']['enable'] == 'yes') {
            $price = !empty($settings['fq']['price']) ? \abs($settings['fq']['price']) : 0;
            $sale = !empty($settings['fq']['sale_price']) ? \abs($settings['fq']['sale_price']) : '';
            $product = \wc_get_product($post_id);
            $product->set_price($price);
            $product->set_regular_price($price);
            $product->set_sale_price($sale);
            $product->set_sold_individually(isset($settings['fq']['sold_individually']) && $settings['fq']['sold_individually'] == 'yes');
            $product->save();
        }
    }
}
/**
 * Helper function to safely get a checkbox post value
 *
 * @access private
 * @since  3.0
 * @param  string $name the checkbox name
 * @return string "yes" or "no" depending on whether the checkbox named $name
 *         was set
 */
function fq_price_calculator_get_checkbox_post($name)
{
    return \wc_clean(\wp_unslash(isset($_POST[$name]) && $_POST[$name] ? 'yes' : 'no'));
}
/**
 * Helper function to safely get overage post value
 *
 * @since 3.12.0
 *
 * @param  string $measurement_type
 * @return int positive number between 0 & 100
 */
function fq_price_calculator_get_overage_post($measurement_type)
{
    $input_name = "_measurement_{$measurement_type}_pricing_overage";
    $input_value = isset($_POST[$input_name]) ? \absint(\wc_clean(\wp_unslash($_POST[$input_name]))) : 0;
    if ($input_value > 100) {
        return 100;
    }
    if ($input_value < 0) {
        return 0;
    }
    return $input_value;
}
/**
 * Helper function to safely get accepted input post value
 *
 * @since 3.12.0
 *
 * @param  string $measurement_type
 * @param  string $input_name
 * @return string
 */
function fq_price_calculator_get_accepted_input_post($measurement_type, $input_name)
{
    $post_name = $measurement_type === $input_name ? "_measurement_{$measurement_type}_accepted_input" : "_measurement_{$measurement_type}_{$input_name}_accepted_input";
    $accepted_input = isset($_POST[$post_name]) ? \sanitize_key($_POST[$post_name]) : '';
    if (!\in_array($accepted_input, ['free', 'limited'])) {
        $accepted_input = 'free';
    }
    return $accepted_input;
}
/**
 * Helper function to safely get input attributes post values
 *
 * @since 3.12.0
 *
 * @param  string $measurement_type
 * @param  string $input_name
 * @return array
 */
function fq_price_calculator_get_input_attributes_post($measurement_type, $input_name)
{
    $post_name = $measurement_type === $input_name ? "_measurement_{$measurement_type}_input_attributes" : "_measurement_{$measurement_type}_{$input_name}_input_attributes";
    $input_attributes = isset($_POST[$post_name]) && \is_array($_POST[$post_name]) ? \array_map('abs', \array_map('floatval', \wc_clean(\wp_unslash($_POST[$post_name])))) : [];
    return \wp_parse_args(\array_filter($input_attributes), ['min' => '', 'max' => '', 'step' => 0]);
}
/**
 * Helper function to safely get measurement options post values
 *
 * @since 3.12.0
 *
 * @param  string $input_name
 * @return array
 */
function fq_price_calculator_get_options_post($input_name)
{
    $input_value = isset($_POST[$input_name]) ? \wc_clean(\wp_unslash($_POST[$input_name])) : '';
    if (empty($input_value)) {
        $values = [];
        // try to explode based on a semi-colon if a semi-colon exists in the input
    } elseif (\WDFQVendorFree\SkyVerge\WooCommerce\PluginFramework\v5_5_0\SV_WC_Helper::str_exists($input_value, ';')) {
        $values = \array_map('trim', \explode(';', $input_value));
    } else {
        $values = \array_map('trim', \explode(',', $input_value));
    }
    return $values;
}
/**
 * Helper function to output limited option set.
 *
 * @since 3.12.8
 *
 * @param  string[] $options original options array
 * @return string delimited options
 */
function fq_price_calculator_get_options_value($options)
{
    $value = null;
    if (',' === \trim(\wc_get_price_decimal_separator())) {
        $value = \implode('; ', $options);
    }
    return $value ? $value : \implode(', ', $options);
}
/**
 * Helper to get the "options" input description.
 *
 * @since 3.12.8
 *
 * @return string description text
 */
function fq_price_calculator_get_options_tooltip()
{
    // use semi-colons if commas are used as the decimal separator
    $delimiter = ',' === \trim(\wc_get_price_decimal_separator()) ? 'semicolon' : 'comma';
    /* translators: Placeholder: %s - delimiter to use in the input */
    $description = \sprintf(\__('Use a single number to set a fixed value for this field on the frontend, or a %s-separated list of numbers to create a select box for the customer to choose between.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $delimiter);
    if ('semicolon' === $delimiter) {
        $description .= ' ' . \__('Example: 1/8; 0,5; 2', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    } else {
        $description .= ' ' . \__('Example: 1/8, 0.5, 2', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    }
    return $description;
}
