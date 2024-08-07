<?php

namespace WDFQVendorFree;

use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig;
use WDFQVendorFree\WPDesk\Library\Marketing\Boxes\MarketingBoxes;
/**
 * @var MarketingBoxes $boxes
 */
$boxes = $params['boxes'] ?? \false;
if (!$boxes) {
    return;
}
if (\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::is_unlocked()) {
    $get_support_url = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage::is_pl_lang() ? 'https://wpde.sk/flexible-quantity-support-pl' : 'https://wpde.sk/flexible-quantity-support';
} else {
    $get_support_url = 'https://wordpress.org/support/plugin/flexible-quantity-measurement-price-calculator-for-woocommerce/';
}
$share_ideas_url = \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage::is_pl_lang() ? 'https://wpde.sk/flexible-quantity-feature-pl' : 'https://wpde.sk/flexible-quantity-feature';
?>
<div class="wrap">
	<div id="marketing-page-wrapper">
		<?php 
echo $boxes->get_boxes()->get_all();
//phpcs:ignore
?>

		<div class="marketing-buttons">
			<a class="button button-primary button-support" target="_blank" href="<?php 
echo \esc_url($get_support_url);
?>"><?php 
\esc_html_e('Get support', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></a>
			<a class="button button-primary button-idea" target="_blank" href="<?php 
echo \esc_url($share_ideas_url);
?>"><?php 
\esc_html_e('Share idea', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></a>
		</div>

		<div class="wpdesk-tooltip-shadow"></div>
		<div id="confirm-support" class="wpdesk-tooltip wpdesk-tooltip-confirm">
			<span class="close-modal close-modal-button"><span class="dashicons dashicons-no-alt"></span></span>
			<h3><?php 
\esc_html_e('Before sending a message please:', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></strong></h3>
			<ul>
				<li><?php 
\esc_html_e('Prepare the information about the version of WordPress, WooCommerce, and Flexible Quantity (preferably your system status from WooCommerce->Status)', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></li>
				<li><?php 
\esc_html_e('Describe the issue you have', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></li>
				<li><?php 
\esc_html_e('Attach any log files & printscreens of the issue', 'flexible-quantity-measurement-price-calculator-for-woocommerce');
?></li>
			</ul>
		</div>
	</div>
</div>
<?php 
