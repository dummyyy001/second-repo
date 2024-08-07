<?php

namespace WDFQVendorFree\WPDesk\Plugin\Flow\Initialization;

use WDFQVendorFree\WPDesk\PluginBuilder\Plugin\SlimPlugin;
/**
 * Interface for initialization strategy for plugin. How to initialize it?
 */
interface InitializationStrategy
{
    /**
     * Run tasks that prepares plugin to work. Have to run before plugin loaded.
     *
     * @param \WPDesk_Plugin_Info $plugin_info
     *
     * @return SlimPlugin
     */
    public function run_before_init(\WDFQVendorFree\WPDesk_Plugin_Info $plugin_info);
    /**
     * Run task that integrates plugin with other dependencies. Can be run in plugins_loaded.
     *
     * @param \WPDesk_Plugin_Info $plugin_info
     *
     * @return SlimPlugin
     */
    public function run_init(\WDFQVendorFree\WPDesk_Plugin_Info $plugin_info);
}
