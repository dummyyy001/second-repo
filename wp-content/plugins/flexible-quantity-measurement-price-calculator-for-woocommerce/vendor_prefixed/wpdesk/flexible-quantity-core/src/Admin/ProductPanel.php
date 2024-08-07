<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin;

use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Helper;
class ProductPanel implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    const FQ_AJAX_ACTION = 'fq_admin_get_dimensions';
    const FQ_AJAX_ADMIN_NONCE = 'fq_admin_nonce';
    /**
     * @var string
     */
    private $plugin_url;
    /**
     * @var string
     */
    private $plugin_path;
    /**
     * @param string $plugin_url
     * @param string $plugin_path
     */
    public function __construct(string $plugin_url, string $plugin_path)
    {
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;
    }
    public function hooks()
    {
        \add_action('admin_init', [$this, 'admin_init']);
        \add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 15);
        // add additional physical property units/measurements
        // add_filter( 'woocommerce_products_general_settings', [ $this, 'catalog_settings', ] );
    }
    function admin_init()
    {
        global $pagenow;
        // on the product new/edit page
        if ('post-new.php' === $pagenow || 'post.php' === $pagenow || \is_ajax()) {
            include_once __DIR__ . '/writepanels/writepanels-init.php';
        }
    }
    /**
     * Enqueue the price calculator admin scripts
     */
    function admin_enqueue_scripts()
    {
        global $taxnow, $post;
        // Get admin screen id
        $screen = \get_current_screen();
        // on the admin product page
        if ($screen && 'product' === $screen->id) {
            \wp_enqueue_script('fq-input-filter', $this->plugin_url . '/assets/js/input-filter.js', [], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
            \wp_enqueue_script('jquery-validate', $this->plugin_url . '/assets/js/vendor/jquery.validate.min.js', ['jquery'], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
            \wp_enqueue_script('fq-admin', $this->plugin_url . '/assets/js/admin/fq-price-calculator.min.js', ['jquery-validate', 'jquery'], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
            \wp_enqueue_script('fq-admin-modal', $this->plugin_url . '/assets/js/modal.js', ['jquery-validate', 'jquery'], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
            // Variables for JS scripts
            $fq_admin_params = ['nonce' => \wp_create_nonce(self::FQ_AJAX_ADMIN_NONCE), 'get_dimensions' => self::FQ_AJAX_ACTION, 'product_id' => $post->ID, 'loader_img' => '<img class="center" src="' . \esc_url(\includes_url() . 'js/tinymce/skins/lightgray/img/loader.gif') . '" />', 'server_error' => \__('We noticed an error while trying to get the dimension table.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'input_filter' => \__('Only digits allowed.', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'woocommerce_currency_symbol' => \get_woocommerce_currency_symbol(), 'woocommerce_weight_unit' => 'no' !== \get_option('woocommerce_enable_weight', \true) ? \get_option('woocommerce_weight_unit') : '', 'pricing_rules_enabled_notice' => \__('Cannot edit price while a pricing table is active', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'is_variable_product_with_stock_managed' => \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Helper::is_variable_product_with_stock_managed(\wc_get_product($post))];
            \wp_localize_script('fq-admin', 'fq_admin_params', $fq_admin_params);
            \wp_enqueue_style('fq-admin-css', $this->plugin_url . '/assets/css/admin.css', [], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
        }
        \wp_enqueue_style('fq-admin-marketing-modal-css', $this->plugin_url . '/assets/css/modal.css', [], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
        \wp_enqueue_style('fq-admin-marketing-css', $this->plugin_url . '/assets/css/marketing.css', [], \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_script_version());
    }
    /**
     * Checks if a given product is variable and has at least one variation with sock management enabled.
     *
     * @param \WC_Product $product
     *
     * @return bool
     * @since 3.18.2
     */
    function fq_price_calculator_is_variable_product_with_stock_managed($product)
    {
        if (!$product instanceof \WC_Product || !$product->is_type('variable')) {
            return \false;
        }
        foreach ($product->get_children() as $variation_id) {
            $variation = \wc_get_product($variation_id);
            if ($variation && $variation->get_manage_stock()) {
                return \true;
            }
        }
        return \false;
    }
}
