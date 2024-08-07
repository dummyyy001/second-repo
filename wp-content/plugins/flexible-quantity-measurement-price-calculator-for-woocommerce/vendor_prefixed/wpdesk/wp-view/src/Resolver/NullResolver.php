<?php

namespace WDFQVendorFree\WPDesk\View\Resolver;

use WDFQVendorFree\WPDesk\View\Renderer\Renderer;
use WDFQVendorFree\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \WDFQVendorFree\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \WDFQVendorFree\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \WDFQVendorFree\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
