<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings;

use WDFQVendorFree\WPDesk\View\Resolver\DirResolver;
use WDFQVendorFree\WPDesk\View\Resolver\ChainResolver;
use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable;
use WDFQVendorFree\WPDesk\View\Renderer\SimplePhpRenderer;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage;
class CustomUnitsMenuPage implements \WDFQVendorFree\WPDesk\PluginBuilder\Plugin\Hookable
{
    const SCRIPTS_VERSION = 2;
    const MENU_PREFIX = 'flexible_quantity';
    const MENU_SLUG = self::MENU_PREFIX . '_custom_units';
    const CUSTOM_UNITS_NONCE = 'fq_custom_units_nonce';
    const CUSTOM_UNITS_ACTION = 'fq_custom_units_action';
    const FORM_UNIT = 'custom_units';
    const FORM_UNIT_NAME = 'name';
    const FQ_OPTION_NAME = 'flexible_quantity_custom_units';
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
            \add_submenu_page(self::MENU_PREFIX, \esc_html__('Custom units', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), \esc_html__('Custom units', 'flexible-quantity-measurement-price-calculator-for-woocommerce'), 'manage_options', self::MENU_SLUG, [$this, 'render_page_action'], 1);
        }, 9999);
        \add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
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
        $this->save_form_data_if_needed();
        $units = \get_option(self::FQ_OPTION_NAME, []);
        $units = \is_array($units) ? $units : [];
        $this->renderer->output_render('custom-units', ['units' => $units, 'is_save' => $this->is_save_form_data_request(), 'custom_units_doc_url' => \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage::is_pl_lang() ? 'https://wpde.sk/fq-custom-units-pl' : 'https://wpde.sk/fq-custom-units']);
    }
    public function is_save_form_data_request()
    {
        return isset($_POST[self::FORM_UNIT]);
    }
    private function save_form_data_if_needed()
    {
        if ($this->is_save_form_data_request() && $this->validate_form_data()) {
            $save_data = [];
            foreach ($_POST[self::FORM_UNIT][self::FORM_UNIT_NAME] as $unit) {
                $unit_name = \sanitize_text_field(\trim($unit));
                if (!empty($unit_name)) {
                    $save_data[][self::FORM_UNIT_NAME] = $unit_name;
                }
            }
            \update_option(self::FQ_OPTION_NAME, $save_data);
        }
    }
    private function validate_form_data()
    {
        if (!\wp_verify_nonce(isset($_POST[self::CUSTOM_UNITS_NONCE]) ? $_POST[self::CUSTOM_UNITS_NONCE] : '', self::CUSTOM_UNITS_ACTION)) {
            \wp_die('Error, security code is not valid');
        }
        if (!\current_user_can('manage_options')) {
            \wp_die('Error, you are not allowed to do this action');
        }
        return \true;
    }
    /**
     * @param string $screen_id
     */
    public function admin_enqueue_scripts($screen_id)
    {
        $screen = \get_current_screen();
        $screen_id = \is_object($screen) ? $screen->id : '';
        if (\strpos($screen_id, self::MENU_SLUG)) {
            \wp_enqueue_style('fq-custom-units', $this->assets_url . 'css/settings.css', [], self::SCRIPTS_VERSION);
            // wp_enqueue_style( 'woocommerce_admin_styles');
            \wp_enqueue_script('jquery-tiptip');
            \wp_enqueue_script('fq-custom-units', $this->assets_url . 'js/settings.js', ['jquery', 'jquery-tiptip'], self::SCRIPTS_VERSION, \true);
        }
    }
}
