<?php

namespace WDFQVendorFree;

// Shipping template
/**
 * @var array $settings
 */
function fq_get_shipping_table_row($index, $settings)
{
    ?>
	<tr>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[shipping_table][items][from][]', 'class' => 'fq_shipping_table_from', 'value' => isset($settings['fq']['shipping_table']['items']['from'][$index]) ? $settings['fq']['shipping_table']['items']['from'][$index] : '', 'wrapper_class' => 'myclass', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
	</td>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[shipping_table][items][to][]', 'value' => isset($settings['fq']['shipping_table']['items']['to'][$index]) ? $settings['fq']['shipping_table']['items']['to'][$index] : '', 'class' => 'fq_shipping_table_to', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
	</td>
	<td>
		<?php 
    $args = ['taxonomy' => 'product_shipping_class', 'hide_empty' => 0, 'show_option_none' => \__('No shipping class', 'woocommerce'), 'name' => 'fq[shipping_table][items][shipping_class][]', 'id' => '', 'selected' => isset($settings['fq']['shipping_table']['items']['shipping_class'][$index]) ? $settings['fq']['shipping_table']['items']['shipping_class'][$index] : '', 'class' => 'select short', 'orderby' => 'name'];
    \wp_dropdown_categories($args);
    ?>
	</td>
	<td style="width: 64px;" class="actions">
		<p>
			<a class="insert" href="#"><span class="dashicons dashicons-insert"></span></a>
			<a class="remove" href="#"><span class="dashicons dashicons-remove"></span></a>
		</p>
	</td>
</tr>
	<?php 
}
?>
<div class="product-panel-item fcm-shipping-panel fq-hidden-panels">
	<div class="measurement-header">
		<h4><?php 
\esc_html_e('Shipping class table', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></h4>
		<?php 
$custom_attributes = [];
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    $custom_attributes = ['disabled' => 'disabled'];
    $settings['fq']['shipping_table']['enable'] = 'no';
}
\woocommerce_wp_checkbox(['id' => 'fq_shipping_table_enable', 'name' => 'fq[shipping_table][enable]', 'value' => isset($settings['fq']['shipping_table']['enable']) && $settings['fq']['shipping_table']['enable'] === 'yes' ? 'yes' : 'no', 'class' => 'checkbox show_table_shipping', 'label' => \false, 'description' => \__('Enable Shipping Class Table. <br><br> The table will allow you to change the shipping class based on the quantity of the new unit of measure added to the cart. The plugin will apply the first condition that is met. The table uses WooCommerce shipping classes. <a href="https://wpde.sk/flexible-quantity-core-shipping" target="_blank">Check the plugin documentation →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'custom_attributes' => $custom_attributes]);
?>
				<p style="font-weight:bold">
<?php 
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    echo \wp_kses_post(\__('To use these settings, <a href="https://www.wpdesk.net/products/flexible-quantity-calculator-for-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-quantity&utm_content=shipping-table">upgrade to PRO →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
}
?>
</p>
	</div>
	<div class="fcm-panel-item-body">
		<table class="widefat fcm-shipping-table" style="">
			<thead>
			<tr>
				<th><?php 
\esc_html_e('From', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
echo \wc_help_tip(\__('The minimum quantity for applying to the new shipping class.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th><?php 
\esc_html_e('To', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
echo \wc_help_tip(\__('The maximum quantity for applying to the new shipping class.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th><?php 
\esc_html_e('Shipping class', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
echo \wc_help_tip(\__('New shipping class.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th style="width: 64px;">&nbsp;</th>
			</tr>
			</thead>
			<tbody>
					<?php 
$items = isset($settings['fq']['shipping_table']['items']['from']) ? $settings['fq']['shipping_table']['items']['from'] : [];
if (empty($items)) {
    \WDFQVendorFree\fq_get_shipping_table_row(0, $settings);
} else {
    foreach ($items as $key => $val) {
        \WDFQVendorFree\fq_get_shipping_table_row($key, $settings);
    }
}
?>
			</tbody>
		</table>
	</div>

</div>
<?php 
