<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin;

use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
class Variable implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    public function hooks()
    {
        \add_action('woocommerce_variation_options_dimensions', [$this, 'product_after_variable_attributes'], 10, 3);
        \add_action('woocommerce_variation_options_pricing', [$this, 'product_variable_pricing'], 10, 3);
        \add_action('woocommerce_process_product_meta_variable', [$this, 'process_product_meta_variable']);
        \add_action('woocommerce_ajax_save_product_variations', [$this, 'process_product_meta_variable']);
    }
    /**
     * Display our custom product Area/Volume meta fields in the product
     * variation form
     *
     * @param int      $loop           the loop index
     * @param array    $variation_data the variation data
     * @param \WP_Post $variation_post the variation post object
     */
    function product_after_variable_attributes($loop, $variation_data, $variation_post)
    {
        global $post;
        $parent_product = \wc_get_product($post);
        // add meta data to $variation_data array
        $variation_product = \wc_get_product($variation_post);
        $variation_data = $variation_product ? \array_merge($variation_product->get_meta_data(), $variation_data) : $variation_data;
        // will use the parent area/volume (if set) as the placeholder
        $parent_data = ['area' => $parent_product ? $parent_product->get_meta('_area') : null, 'volume' => $parent_product ? $parent_product->get_meta('_volume') : null];
        // default placeholders
        if (!$parent_data['area']) {
            $parent_data['area'] = '0.00';
        }
        if (!$parent_data['volume']) {
            $parent_data['volume'] = '0';
        }
        ?>
		<p class="form-row form-row-first hide_if_variation_virtual">
			<label>
				<?php 
        echo \esc_html('Area', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . ' (' . \esc_html(\get_option('woocommerce_area_unit')) . '):';
        ?>
				<?php 
        echo \wc_help_tip(\wp_kses_post(\__('Overrides the area calculated from the width/length dimensions for the Measurements Price Calculator.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
        //phpcs:ignore
        ?>
			</label>
			<input type="number" size="5" name="variable_area[<?php 
        echo \esc_attr($loop);
        ?>]"
					value="
					<?php 
        if (isset($variation_data['_area'][0])) {
            echo \esc_attr($variation_data['_area'][0]);
        }
        ?>
					" placeholder="<?php 
        echo \esc_attr($parent_data['area']);
        ?>" step="any" min="0"/>
		</p>
		<p class="form-row form-row-last hide_if_variation_virtual">
			<label>
				<?php 
        echo \esc_html('Volume', 'flexible-quantity-measurement-price-calculator-for-woocommerce') . ' (' . \esc_html(\get_option('woocommerce_volume_unit')) . '):';
        ?>
				<?php 
        echo \wc_help_tip(\wp_kses_post(\__('Overrides the volume calculated from the width/length/height dimensions for the Measurements Price Calculator.', 'flexible-quantity-measurement-price-calculator-for-woocommerce')));
        //phpcs:ignore
        ?>
			</label>
			<input type="number" size="5" name="variable_volume[<?php 
        echo \esc_attr($loop);
        ?>]"
					value="
					<?php 
        if (isset($variation_data['_volume'][0])) {
            echo \esc_attr($variation_data['_volume'][0]);
        }
        ?>
					" placeholder="<?php 
        echo \esc_html($parent_data['volume']);
        ?>" step="any" min="0"/>
		</p>
		<?php 
    }
    /**
     * Displays our custom minimum price field in the product variation form.
     *
     * @param int      $loop           the loop index
     * @param array    $variation_data the variation data
     * @param \WP_Post $variation_post the variation post object
     *
     * @since 3.16.0
     * @internal
     */
    function product_variable_pricing($loop, $variation_data, $variation_post)
    {
        // add meta data to $variation_data array
        $variation_product = \wc_get_product($variation_post);
        $variation_data = $variation_product ? \array_merge($variation_product->get_meta_data(), $variation_data) : $variation_data;
        $default_value = isset($variation_data['_fq_price_calculator_min_price']) ? \current($variation_data['_fq_price_calculator_min_price']) : null;
        \woocommerce_wp_text_input([
            'id' => 'variable_min_price[' . $loop . ']',
            'name' => 'variable_min_price[' . $loop . ']',
            'wrapper_class' => 'show_if_pricing_calculator',
            'class' => 'wc_input_price short',
            /* translators: Placeholder: %s - currency symbol */
            'label' => \sprintf(\__('Minimum Price (%s)', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \get_woocommerce_currency_symbol()),
            'type' => 'number',
            'custom_attributes' => ['step' => 'any', 'min' => '0'],
            'value' => $default_value,
        ]);
    }
    /**
     * Save the variable product options.
     *
     * @param mixed $post_id the post identifier
     */
    function process_product_meta_variable($post_id)
    {
        if (isset($_POST['variable_sku'])) {
            $variable_post_id = isset($_POST['variable_post_id']) ? \wc_clean(\wp_unslash($_POST['variable_post_id'])) : [];
            $variable_area = isset($_POST['variable_area']) ? \wc_clean(\wp_unslash($_POST['variable_area'])) : '';
            $variable_volume = isset($_POST['variable_volume']) ? \wc_clean(\wp_unslash($_POST['variable_volume'])) : '';
            $variable_min_price = isset($_POST['variable_min_price']) ? \wc_clean(\wp_unslash($_POST['variable_min_price'])) : '';
            // bail if $variable_post_id is not as expected
            if (!\is_array($variable_post_id)) {
                return;
            }
            $max_loop = \max(\array_keys($variable_post_id));
            for ($i = 0; $i <= $max_loop; $i++) {
                if (!isset($variable_post_id[$i])) {
                    continue;
                }
                $variation_id = (int) $variable_post_id[$i];
                $variation = \wc_get_product($variation_id);
                // Update area post meta
                if (empty($variable_area[$i])) {
                    $variation->delete_meta_data('_area');
                } else {
                    $variation->update_meta_data('_area', $variable_area[$i]);
                }
                // Update volume post meta
                if (empty($variable_volume[$i])) {
                    $variation->delete_meta_data('_volume');
                } else {
                    $variation->update_meta_data('_volume', $variable_volume[$i]);
                }
                // Update minimum price post meta
                if (empty($variable_min_price[$i])) {
                    $variation->delete_meta_data('_fq_price_calculator_min_price');
                } else {
                    $variation->update_meta_data('_fq_price_calculator_min_price', $variable_min_price[$i]);
                }
                $variation->save_meta_data();
            }
        }
    }
}
