<?php

namespace WDFQVendorFree;

\defined('ABSPATH') || exit;
/**
 * Product page measurement pricing calculator.
 *
 * @global \WC_Product $product the product
 * @type   \WC_Price_Calculator_Measurement[] $measurements array of measurements
 * @type   \WC_Price_Calculator_Measurement $product_measurement the measurement
 * @type   \WC_Price_Calculator_Settings $settings calculator settings
 * @type   float $default_step default step value based on calculator precision
 * @type   string $calculator_mode the current calculator mode for the product
 *
 * @version 3.13.5
 * @since   1.0.0
 */
global $product;
$total_amount_text = \apply_filters('fq_price_calculator_total_amount_text', $product_measurement->get_unit_label() ? \sprintf(\__('Total %1$s (%2$s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $product_measurement->get_label(), \__($product_measurement->get_unit_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce')) : \sprintf(\__('Total %s', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $product_measurement->get_label()), $product);
// pricing overage
$pricing_overage = $settings->get_pricing_overage();
$has_pricing_overage = $pricing_overage > 0;
?>
<table id="price_calculator" class="wc-measurement-price-calculator-price-table <?php 
echo \esc_html($product->get_type() . '_price_calculator') . ' ' . \esc_html($calculator_mode);
?>">
	<?php 
foreach ($measurements as $measurement) {
    $fixed_measurement_value = '';
    $input_attributes = $settings->get_input_attributes($measurement->get_name());
    if (isset($input_attributes['min']) && \is_numeric($input_attributes['min'])) {
        $fixed_measurement_value = $input_attributes['min'];
    }
    if ($fixed_measurement_value == '' && isset($input_attributes['max']) && \is_numeric($input_attributes['max'])) {
        $fixed_measurement_value = $input_attributes['max'];
    }
    if ($fixed_measurement_value == '' && isset($input_attributes['step']) && \is_numeric($input_attributes['step'])) {
        $fixed_measurement_value = $input_attributes['step'];
    }
    $measurement_name = $measurement->get_name() . '_needed';
    $fixed_measurement_value = $fixed_measurement_value == '' ? $measurement->get_default_value() !== null ? $measurement->get_default_value() : '' : $fixed_measurement_value;
    $measurement_value = isset($_POST[$measurement_name]) ? \wc_clean(\wp_unslash($_POST[$measurement_name])) : $fixed_measurement_value;
    //phpcs:ignore
    $measurement_options = $measurement->get_options();
    $input_accepted = $settings->get_accepted_input($measurement->get_name());
    $attributes = [];
    $help_tooltip = '';
    if (empty($input_attributes)) {
        // default text input field
        $input_type = 'text';
    } else {
        // numeric input field
        $input_type = 'number';
        if (!isset($input_attributes['step'])) {
            $input_attributes['step'] = $default_step;
        }
        if (!isset($input_attributes['min'])) {
            $input_attributes['min'] = 0;
        }
        // convert to HTML attributes
        foreach ($input_attributes as $key => $value) {
            $attributes[] = $key . '="' . $value . '"';
        }
    }
    if ('text' === $input_type) {
        $decimal_separator = \trim(\wc_get_price_decimal_separator());
        $thousand_separator = \trim(\wc_get_price_thousand_separator());
        $format_example = "1{$thousand_separator}234{$decimal_separator}56";
        /* translators: Placeholder: %s - format example */
        $help_text = \sprintf(\__('Please enter the desired amount with this format: %s', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $format_example);
    } else {
        $help_text = '';
    }
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
        echo \wp_kses_post(\sprintf(\__('%1$s (%2$s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $measurement->get_label(), \__($measurement->get_unit_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
    } else {
        echo \wp_kses_post(\__($measurement->get_label(), 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
    }
    ?>
				</label>
			</td>

			<td style="text-align:right;">

		<?php 
    if ('' !== $help_text) {
        ?>

			<?php 
        $help_tooltip = '<span class="dashicons dashicons-editor-help wc-measurement-price-calculator-input-help tip" title="' . \esc_html($help_text) . '"></span>';
        ?>

		<?php 
    }
    ?>

		<?php 
    if ('user' === $input_accepted) {
        ?>

			<?php 
        echo \wp_kses_post($help_tooltip);
        ?>

					<input
			<?php 
        echo $measurement->is_editable() ? '' : 'readonly';
        ?>
						type="number"
						name="<?php 
        echo \esc_attr($measurement_name);
        ?>"
						id="<?php 
        echo \esc_attr($measurement_name);
        ?>"
						class="amount_needed"
						value="<?php 
        echo \esc_attr($measurement_value);
        ?>"
						data-unit="<?php 
        echo \esc_attr($measurement->get_unit());
        ?>"
						data-common-unit="<?php 
        echo \esc_attr($measurement->get_unit_common());
        ?>"
						autocomplete="off"
			<?php 
        echo \wp_kses_post(\implode(' ', $attributes));
        ?>
					/>

		<?php 
    }
    ?>

			</td>
		</tr>

	<?php 
}
?>

	<?php 
if ($settings->is_calculator_type_derived()) {
    ?>

		<tr class="price-table-row total-amount">
			<td>
		<?php 
    // echo wp_kses_post($total_amount_text);
    ?>
			</td>
			<td>
				<span
					class="wc-measurement-price-calculator-total-amount"
					data-unit="<?php 
    echo \esc_attr($product_measurement->get_unit());
    ?>"></span>
			</td>
		</tr>

	<?php 
}
?>

	<?php 
if ($has_pricing_overage) {
    ?>

		<tr class="price-table-row calculated-price-overage">
			<td><?php 
    echo \esc_html(\sprintf(\__('Overage estimate (%s%%)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), $pricing_overage * 100));
    ?></td>
			<td>
				<span class="product_price_overage"></span>
			</td>
		</tr>

	<?php 
}
?>

	<tr class="price-table-row calculated-price">

		<td><?php 
echo \esc_html($has_pricing_overage ? \__('Total Price', 'flexible-quantity-measurement-price-calculator-for-woocommerce') : \__('Product Price', 'flexible-quantity-measurement-price-calculator-for-woocommerce'));
?></td>

		<td>

			<span class="product_price"></span>
			<input
				type="hidden"
				id="_measurement_needed"
				name="_measurement_needed"
				value=""
			/>
			<input
				type="hidden"
				id="_measurement_needed_unit"
				name="_measurement_needed_unit"
				value=""
			/>

			<?php 
if ($product->is_sold_individually()) {
    ?>

				<input
					type="hidden"
					name="quantity"
					value="1"
				/>

			<?php 
}
?>

		</td>

	</tr>

</table>
<?php 
