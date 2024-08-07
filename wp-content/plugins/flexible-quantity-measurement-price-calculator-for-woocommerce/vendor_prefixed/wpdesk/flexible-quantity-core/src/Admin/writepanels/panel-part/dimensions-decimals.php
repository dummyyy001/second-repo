<?php

namespace WDFQVendorFree;

// Dimensions template
?>
<div class="product-panel-item fcm-dimensions-decimals-panel fq-hidden-panels dimensions">
	<div class="measurement-header">
		<h4><?php 
\esc_html_e('Unit Dimensions', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></h4>
		<?php 
$custom_attributes = [];
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    $custom_attributes = ['disabled' => 'disabled'];
    $settings['fq']['decimals_enabled'] = 'no';
}
\woocommerce_wp_checkbox(['id' => 'fq_decimals_enabled', 'name' => 'fq[decimals_enabled]', 'value' => isset($settings['fq']['decimals_enabled']) ? $settings['fq']['decimals_enabled'] : 'no', 'class' => 'fq_decimals_enabled', 'label' => \false, 'description' => \wp_kses_post(\__('Enable Advanced Calculator Settings. <br><br> Use the extended options to specify the unit dimensions and allow the buyer to enter them. The price will be calculated on the product page. You can use the following settings to set a fixed dimension value or enable user input. Need more information? <a href="https://wpde.sk/flexible-quantity-core-units" target="_blank">Check the plugin documentation →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce')), 'custom_attributes' => $custom_attributes]);
?>
		<p style="font-weight:bold">
<?php 
if (!\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    echo \wp_kses_post(\__('To use these settings, <a href="https://www.wpdesk.net/products/flexible-quantity-calculator-for-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-quantity&utm_content=unit-dimensions">upgrade to PRO →</a>', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
}
?>
</p>
	</div>
	<div id="fq-decimals-panel" class="fcm-panel-item-body fcm-body-decimal">
		<?php 
echo \WDFQVendorFree\fq_generate_decimals_template($settings);
?>
	</div>
</div>
<?php 
