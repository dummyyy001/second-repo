<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing;

use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig;
use WDFQVendorFree\WPDesk\Library\Marketing\Boxes\Assets;
use WDFQVendorFree\WPDesk\Library\Marketing\Boxes\MarketingBoxes;
use WDFQVendorFree\WPDesk\Library\Marketing\RatePlugin\RateBox;
use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
use WDFQVendorFree\WPDesk\View\Renderer\SimplePhpRenderer;
use WDFQVendorFree\WPDesk\View\Resolver\ChainResolver;
use WDFQVendorFree\WPDesk\View\Resolver\DirResolver;
class SupportMenuPage implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    const SCRIPTS_VERSION = 2;
    const MENU_POSITION = 27;
    const MENU_PREFIX = 'flexible_quantity';
    const MENU_INSTRUCTIONS = self::MENU_PREFIX . '_instructions';
    /**
     * @var string
     */
    private $assets_url;
    /**
     * @var SimplePhpRenderer
     */
    private $renderer;
    public function __construct(string $assets_url)
    {
        $this->assets_url = $assets_url;
        $this->init_renderer();
    }
    public function hooks()
    {
        \add_action('admin_menu', function () {
            global $submenu;
            \add_menu_page(\esc_html_x('Flexible Quantity', 'main_menu', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \esc_html_x('Flexible Quantity', 'main_menu', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'manage_options', self::MENU_PREFIX, [$this, 'render_page_action'], 'dashicons-calculator', self::MENU_POSITION);
            $submenu[self::MENU_PREFIX] = [];
            \add_submenu_page(self::MENU_PREFIX, \esc_html__('How to use', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \esc_html__('How to use', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'manage_options', self::MENU_INSTRUCTIONS, [$this, 'render_page_action'], 0);
        }, 9999);
        \add_action('admin_footer', [$this, 'append_plugin_rate']);
        \add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        \WDFQVendorFree\WPDesk\Library\Marketing\Boxes\Assets::enqueue_assets();
        \WDFQVendorFree\WPDesk\Library\Marketing\Boxes\Assets::enqueue_owl_assets();
    }
    /**
     * Init renderer.
     */
    private function init_renderer()
    {
        $resolver = new \WDFQVendorFree\WPDesk\View\Resolver\ChainResolver();
        $resolver->appendResolver(new \WDFQVendorFree\WPDesk\View\Resolver\DirResolver(__DIR__ . '/Views/'));
        $this->renderer = new \WDFQVendorFree\WPDesk\View\Renderer\SimplePhpRenderer($resolver);
    }
    public function render_page_action()
    {
        $local = \get_locale();
        if ($local === 'en_US') {
            $local = 'en';
        }
        $boxes = new \WDFQVendorFree\WPDesk\Library\Marketing\Boxes\MarketingBoxes(\WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\PluginConfig::get_marketing_slug(), $local);
        $this->renderer->output_render('marketing-page', ['boxes' => $boxes]);
    }
    /**
     * @return bool
     */
    private function should_show_rate_notice() : bool
    {
        global $current_screen;
        return $current_screen->post_type === 'inspire_invoice';
    }
    /**
     * Add plugin rate box to settings & support page
     */
    public function append_plugin_rate()
    {
        if ($this->should_show_rate_notice()) {
            $rate_box = new \WDFQVendorFree\WPDesk\Library\Marketing\RatePlugin\RateBox();
            $this->renderer->output_render('rate-box-footer', ['rate_box' => $rate_box]);
        }
    }
    /**
     * @param string $screen_id
     */
    public function admin_enqueue_scripts($screen_id)
    {
        if (\in_array($screen_id, ['inspire_invoice_page_wpdesk-marketing'], \true)) {
            \wp_enqueue_style('marketing-page', $this->assets_url . 'css/marketing.css', [], self::SCRIPTS_VERSION);
            \wp_enqueue_script('marketing-page', $this->assets_url . 'js/modal.js', ['jquery'], self::SCRIPTS_VERSION, \true);
        }
    }
    public static function is_pl_lang()
    {
        return \get_locale() === 'pl_PL' || \get_locale() === 'pl';
    }
}
