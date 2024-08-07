<?php

/**
 * Plugin main class.
 *
 * @package WPDesk\Library\FlexibleQuantityCore
 */
namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore;

use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage;
use WDFQVendorFree\WPDesk_Plugin_Info;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Cart;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Compatibility;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Inventory;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\ProductLoop;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\ProductPage;
use WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Shortcodes;
/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @codeCoverageIgnore
 */
class PluginConfig
{
    /**
     * @var string
     */
    private $plugin_url;
    /**
     * @var string
     */
    private $plugin_path;
    /**
     * @var string
     */
    private $core_path;
    private static $script_version = '1.0.0';
    private static $unlocked = \false;
    private static $full_core_path = '';
    private static $marketing_slug = '';
    public function __construct(string $plugin_url, string $plugin_path, string $core_path, string $script_version, string $marketing_slug, $unlocked = \false)
    {
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;
        $this->core_path = $core_path;
        self::$full_core_path = $plugin_path . $core_path;
        self::$script_version = $script_version;
        self::$unlocked = $unlocked;
        self::$marketing_slug = $marketing_slug;
    }
    public function get_hookable_elements() : array
    {
        $product_page = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\ProductPage($this->plugin_url . $this->core_path);
        $hooks = [new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Admin\ProductPanel($this->plugin_url . $this->core_path, $this->plugin_path . $this->core_path), new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Inventory(), new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\ProductLoop(), $product_page, new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Cart(), new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Shortcodes(), new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Compatibility($product_page), new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Marketing\SupportMenuPage($this->plugin_url . $this->core_path . '/assets/')];
        if (self::$unlocked === \true) {
            $hooks[] = new \WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\Settings\CustomUnitsMenuPage($this->plugin_url . $this->core_path . '/assets/');
        }
        return $hooks;
    }
    public static function is_unlocked()
    {
        return self::$unlocked;
    }
    public static function get_script_version()
    {
        return self::$script_version;
    }
    public static function get_full_core_path()
    {
        return self::$full_core_path;
    }
    public static function get_marketing_slug()
    {
        return self::$marketing_slug;
    }
}
