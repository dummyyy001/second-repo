<?php

namespace WDFQVendorFree;

\defined('ABSPATH') or exit;
/**
 * Product page measurement quantity calculator.
 *
 * @global \WC_Product $product the product
 * @type \WC_Price_Calculator_Measurement[] $measurements array of measurements
 * @type \WC_Price_Calculator_Measurement $product_measurement the measurement
 * @type string $calculator_mode current calculator mode
 * @type string $total_price the total calculated price
 *
 * @version 3.13.5
 * @since 2.0
 */
global $product;
$actual_amount_text = \apply_filters('fq_price_calculator_actual_amount_text', $product_measurement->get_unit_label() ? \sprintf(\__('Actual %1$s (%2$s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__($product_measurement->get_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__($product_measurement->get_unit_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce')) : \sprintf(\__('Actual %s', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \__($product_measurement->get_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
?>
<table id="price_calculator" class="wc-measurement-price-calculator-price-table <?php 
echo \esc_html($product->get_type() . '_price_calculator') . ' ' . \esc_html($calculator_mode);
?>">
	<?php 
foreach ($measurements as $measurement) {
    ?>

		<?php 
    if ($measurement->is_editable()) {
        ?>

			<?php 
        $measurement_name = $measurement->get_name() . '_needed';
        ?>

			<tr class="price-table-row <?php 
        echo \esc_html($measurement->get_name());
        ?>-input">

				<td>
					<label for="<?php 
        echo \esc_attr($measurement_name);
        ?>">
						<?php 
        if ($measurement->get_unit_label()) {
            /* translators: Placeholders: %1$s - measurement label, %2$s - measurement unit label */
            echo \wp_kses_post(\sprintf(\__('%1$s (%2$s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $measurement->get_label(), \__($measurement->get_unit_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
        } else {
            echo \wp_kses_post(\__($measurement->get_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
        }
        ?>
					</label>
				</td>

				<td style="text-align:right;">
					<?php 
        $decimal_separator = \trim(\wc_get_price_decimal_separator());
        $thousand_separator = \trim(\wc_get_price_thousand_separator());
        $format_example = "1{$thousand_separator}234{$decimal_separator}56";
        /* translators: Placeholder: %s - format example */
        $help_text = \sprintf(\__('Please enter the desired amount with this format: %s', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $format_example);
        ?>
					<span class="dashicons dashicons-editor-help wc-measurement-price-calculator-input-help tip" title="<?php 
        echo \esc_html($help_text);
        ?>"></span>
					<input
						type="text"
						name="<?php 
        echo \esc_attr($measurement_name);
        ?>"
						id="<?php 
        echo \esc_attr($measurement_name);
        ?>"
						class="amount_needed"
						data-unit="<?php 
        echo \esc_attr($measurement->get_unit());
        ?>"
						data-common-unit="<?php 
        echo \esc_attr($measurement->get_unit_common());
        ?>"
						autocomplete="off"
					/>
				</td>

			</tr>
		<?php 
    }
    ?>

	<?php 
}
?>

	<tr class="price-table-row total-amount">
		<td>
			<?php 
echo \wp_kses_post($actual_amount_text);
?>
		</td>
		<td>
			<span id="<?php 
echo \esc_attr($product_measurement->get_name());
?>_actual" class="amount_actual" data-unit="<?php 
echo \esc_attr($product_measurement->get_unit());
?>"><?php 
echo \wp_kses_post($product_measurement->get_value());
?></span>
		</td>
	</tr>

	<tr class="price-table-row calculated-price">
		<td>
			<?php 
\esc_html_e('Total Price', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?>
		</td>
		<td>
			<span class="total_price"><?php 
echo \wp_kses_post($total_price);
?></span>
		</td>
	</tr>

</table>
<?php 
