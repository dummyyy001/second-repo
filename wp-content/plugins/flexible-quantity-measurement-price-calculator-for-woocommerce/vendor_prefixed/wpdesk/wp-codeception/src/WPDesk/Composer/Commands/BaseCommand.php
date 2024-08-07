<?php

namespace WDFQVendorFree\WPDesk\Composer\Codeception\Commands;

use WDFQVendorFree\Composer\Command\BaseCommand as CodeceptionBaseCommand;
use WDFQVendorFree\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base for commands - declares common methods.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
abstract class BaseCommand extends \WDFQVendorFree\Composer\Command\BaseCommand
{
    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function execAndOutput($command, \WDFQVendorFree\Symfony\Component\Console\Output\OutputInterface $output)
    {
        \passthru($command);
    }
}
