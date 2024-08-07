<?php

namespace WDFQVendorFree;

\defined('ABSPATH') or exit;
use WDFQVendorFree\SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings;
global $post;
?>
<table class="widefat calculator-pricing-table">
	<thead>
	<tr>
		<th class="check-column"><input type="checkbox"></th>
		<th class="measurement-range-column">
			<span class="column-title" data-text="<?php 
\esc_attr_e('Measurement Range', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?>"><?php 
\esc_html_e('Measurement Range', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></span>
			<?php 
echo \wc_help_tip(\wp_kses_post(\__('Configure the starting-ending range, inclusive, of measurements to match this rule.  The first matched rule will be used to determine the price.  The final rule can be defined without an ending range to match all measurements greater than or equal to its starting range.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
//phpcs:ignore
?>
		</th>
		<th class="price-per-unit-column">
			<span class="column-title" data-text="<?php 
\esc_attr_e('Price per Unit', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?>"><?php 
\esc_html_e('Price per Unit', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></span>
			<?php 
echo \wc_help_tip(\wp_kses_post(\__('Set the price per unit for the configured range.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
//phpcs:ignore
?>
		</th>
		<th class="sale-price-per-unit-column">
			<span class="column-title" data-text="<?php 
\esc_attr_e('Sale Price per Unit', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?>"><?php 
\esc_html_e('Sale Price per Unit', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></span>
			<?php 
echo \wc_help_tip(\wp_kses_post(\__('Set a sale price per unit for the configured range.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
//phpcs:ignore
?>
		</th>
	</tr>
	</thead>
	<tbody>
		<?php 
$settings = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Settings(\wc_get_product($post->ID));
$raw_settings = $settings->get_settings();
$rules = $product ? $product->get_meta('_wc_price_calculator_pricing_rules') : null;
if (!empty($rules)) {
    $index = 0;
    foreach ($rules as $rule) {
        ?>
				<tr class="fq-calculator-pricing-rule">
					<td class="check-column">
						<input type="checkbox" name="select" />
					</td>
					<td class="fq-calculator-pricing-rule-range">
						<input type="text" name="_fq_pricing_rule_range_start[<?php 
        echo \esc_attr($index);
        ?>]" value="<?php 
        echo \esc_html($rule['range_start']);
        ?>" /> -
						<input type="text" name="_fq_pricing_rule_range_end[<?php 
        echo \esc_attr($index);
        ?>]" value="<?php 
        echo \esc_html($rule['range_end']);
        ?>" />
					</td>
					<td>
						<input type="text" name="_fq_pricing_rule_regular_price[<?php 
        echo \esc_attr($index);
        ?>]" value="<?php 
        echo \esc_html($rule['regular_price']);
        ?>" />
					</td>
					<td>
						<input type="text" name="_fq_pricing_rule_sale_price[<?php 
        echo \esc_attr($index);
        ?>]" value="<?php 
        echo \esc_html($rule['sale_price']);
        ?>" />
					</td>
				</tr>
				<?php 
        ++$index;
    }
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="4">
				<button type="button" class="button button-primary fq-calculator-pricing-table-add-rule"><?php 
\esc_html_e('Add Rule', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></button>
				<button type="button" class="button button-secondary fq-calculator-pricing-table-delete-rules"><?php 
\esc_html_e('Delete Selected', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></button>
			</th>
		</tr>
	</tfoot>
</table>
<?php 
