<?php

namespace WDFQVendorFree;

/**
 * @var array<int, <string, string>> $units
 *
 * @var bool $is_save
 *
 * @var string $custom_units_doc_url
 */
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage;
function fq_get_form_row($field_name, $disabled = \true)
{
    ?>
	<div class="wrap-condition single-condition flex-row">
		<div class="flex-col width-100">
			<div class="flex-container">
				<div class="flex-row stretch flex-fields">
					<input type="text" name="custom_units[name][]" class="width-100" placeholder="<?php 
    \_e('Product unit name', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?>" value="<?php 
    echo $field_name;
    ?>">
				</div>
			</div>
		</div>
		<div class="flex-col"><span class="fq-help-tip dashicons" data-tip="<?php 
    echo \__('The name of the unit will be visible in the list in the product edition, in the Flexible Quantity tab', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?>"></span></div>
		<div class="flex-col"><a href="#" class="add-condition"><span class="dashicons dashicons-plus-alt"></span></a></div>
		<div class="flex-col"><a href="#" class="remove-condition <?php 
    echo $disabled === \true ? 'disabled' : '';
    ?>"><span class="dashicons dashicons-remove"></span></a></div>
	</div>
	<?php 
}
?>

<form action="" method="post">
	<?php 
\wp_nonce_field(\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage::CUSTOM_UNITS_ACTION, \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage::CUSTOM_UNITS_NONCE);
?>


	<?php 
if ($is_save) {
    ?>
		<div id="message" class="updated fade"><p><strong><?php 
    \_e('Settings are saved', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
    ?></strong></p></div>
	<?php 
}
?>

	<h3><?php 
\_e('Flexible Quantity - Custom Units', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></h3>

	<p><?php 
\_e('Below you can add custom units, which will be added to the list of units next to the product.', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?> <a href="<?php 
echo \esc_url($custom_units_doc_url);
?>" target="_blank"><?php 
\_e('Check out plugins docs to learn how to set up custom units for WooCommerce products &rarr;', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></a></p>

<div id="fq-custom-units-wrapper">
<div id="fq-custom-units" class="flex-container odd">
	<?php 
if (empty($units)) {
    \WDFQVendorFree\fq_get_form_row('');
} else {
    $disabled = \true;
    foreach ($units as $unit) {
        \WDFQVendorFree\fq_get_form_row($unit[\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage::FORM_UNIT_NAME], $disabled);
        $disabled = \false;
    }
}
?>
</div>
<p class="submit"><input type="submit" value="<?php 
\_e('Save settings', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?>" class="button button-primary" id="submit" name="submitForm"></p>
</div>
</form>
<?php 
