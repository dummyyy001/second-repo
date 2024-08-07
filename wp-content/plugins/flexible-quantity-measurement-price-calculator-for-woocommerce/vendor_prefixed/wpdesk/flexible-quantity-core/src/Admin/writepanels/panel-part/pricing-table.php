<?php

namespace WDFQVendorFree;

// Pricing table template
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig;
/**
 * @var array $settings
 */
function fq_get_price_table_row($index, $settings)
{
    ?>
	<tr>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[pricing_table][items][from][]', 'class' => 'fq_pricing_table_from', 'value' => isset($settings['fq']['pricing_table']['items']['from'][$index]) ? $settings['fq']['pricing_table']['items']['from'][$index] : '', 'wrapper_class' => 'myclass', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '1', 'min' => '0']]);
    ?>
	</td>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[pricing_table][items][to][]', 'value' => isset($settings['fq']['pricing_table']['items']['to'][$index]) ? $settings['fq']['pricing_table']['items']['to'][$index] : '', 'class' => 'fq_pricing_table_to', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '1', 'min' => '0']]);
    ?>
	</td>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[pricing_table][items][price][]', 'value' => isset($settings['fq']['pricing_table']['items']['price'][$index]) ? $settings['fq']['pricing_table']['items']['price'][$index] : '', 'class' => 'fq_pricing_table_price', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
    ?>
	</td>
	<td>
		<?php 
    \woocommerce_wp_text_input(['id' => '', 'name' => 'fq[pricing_table][items][sale_price][]', 'value' => isset($settings['fq']['pricing_table']['items']['sale_price'][$index]) ? $settings['fq']['pricing_table']['items']['sale_price'][$index] : '', 'class' => 'fq_pricing_table_sale_price', 'label' => \false, 'placeholder' => '', 'type' => 'number', 'custom_attributes' => ['step' => '0.01', 'min' => '0']]);
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

<div class="product-panel-item fcm-pricing-panel fq-hidden-panels">
	<div class="measurement-header">
		<h4><?php 
\esc_html_e('Pricing table', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></h4>
		<?php 
$custom_attributes = [];
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    $custom_attributes = ['disabled' => 'disabled'];
    $settings['fq']['pricing_table']['enable'] = 'no';
}
\woocommerce_wp_checkbox(['id' => 'fq_pricing_table_enable', 'name' => 'fq[pricing_table][enable]', 'value' => isset($settings['fq']['pricing_table']['enable']) && $settings['fq']['pricing_table']['enable'] === 'yes' ? 'yes' : 'no', 'class' => 'checkbox fq_pricing_table_enable show_table_pricing', 'label' => \false, 'description' => \wp_kses_post(\__('Enable Pricing Table <br><br> The table will allow you to determine the different prices of the unit of measurement based on its quantity. Set the quantity range, and the regular and the sale price. The plugin will apply the first met condition from the table. <a href="https://wpde.sk/flexible-quantity-core-pricing" target="_blank">Check the plugin documentation →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce')), 'custom_attributes' => $custom_attributes]);
?>
			<p style="font-weight:bold">
<?php 
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    echo \wp_kses_post(\__('To use these settings, <a href="https://www.wpdesk.net/products/flexible-quantity-calculator-for-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-quantity&utm_content=pricing-table">upgrade to PRO →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
}
?>
</p>
	</div>
	<div class="fcm-panel-item-body">
		<table class="widefat fcm-pricing-table" style="">
			<thead>
			<tr>
				<th><?php 
\esc_html_e('From', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
echo \wc_help_tip(\__('The minimum quantity to apply the new price for the unit of measure.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th><?php 
\esc_html_e('To', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
echo \wc_help_tip(\__('The maximum quantity to apply the new price for the unit of measure.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th><?php 
\esc_html_e('Price', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?> (<?php 
echo \get_woocommerce_currency_symbol();
?>)<?php 
echo \wc_help_tip(\__('New regular price per unit.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th><?php 
\esc_html_e('Sale Price', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?> (<?php 
echo \get_woocommerce_currency_symbol();
?>)<?php 
echo \wc_help_tip(\__('New sale price per unit.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \true);
?></th>
				<th style="width: 64px;">&nbsp;</th>
			</tr>
			</thead>
			<tbody>
					<?php 
$items = isset($settings['fq']['pricing_table']['items']['from']) ? $settings['fq']['pricing_table']['items']['from'] : [];
if (empty($items)) {
    \WDFQVendorFree\fq_get_price_table_row(0, $settings);
} else {
    foreach ($items as $key => $val) {
        \WDFQVendorFree\fq_get_price_table_row($key, $settings);
    }
}
?>
			</tbody>
		</table>
	</div>

</div>
<?php 
