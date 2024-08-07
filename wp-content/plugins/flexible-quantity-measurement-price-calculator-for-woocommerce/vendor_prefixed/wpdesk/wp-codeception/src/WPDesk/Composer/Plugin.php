<?php

namespace WDFQVendorFree\WPDesk\Composer\Codeception;

use WDFQVendorFree\Composer\Composer;
use WDFQVendorFree\Composer\IO\IOInterface;
use WDFQVendorFree\Composer\Plugin\Capable;
use WDFQVendorFree\Composer\Plugin\PluginInterface;
/**
 * Composer plugin.
 *
 * @package WPDesk\Composer\Codeception
 */
class Plugin implements \WDFQVendorFree\Composer\Plugin\PluginInterface, \WDFQVendorFree\Composer\Plugin\Capable
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    public function activate(\WDFQVendorFree\Composer\Composer $composer, \WDFQVendorFree\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function deactivate(\WDFQVendorFree\Composer\Composer $composer, \WDFQVendorFree\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function uninstall(\WDFQVendorFree\Composer\Composer $composer, \WDFQVendorFree\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    public function getCapabilities()
    {
        return [\WDFQVendorFree\Composer\Plugin\Capability\CommandProvider::class => \WDFQVendorFree\WPDesk\Composer\Codeception\CommandProvider::class];
    }
}
