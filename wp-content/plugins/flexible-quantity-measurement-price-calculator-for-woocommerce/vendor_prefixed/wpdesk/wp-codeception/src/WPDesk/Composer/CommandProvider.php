<?php

namespace WDFQVendorFree\WPDesk\Composer\Codeception;

use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTestsWithCoverage;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests;
use WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTestsWithCoverage;
/**
 * Links plugin commands handlers to composer.
 */
class CommandProvider implements \WDFQVendorFree\Composer\Plugin\Capability\CommandProvider
{
    public function getCommands()
    {
        return [new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTestsWithCoverage(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTestsWithCoverage(), new \WDFQVendorFree\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests()];
    }
}
