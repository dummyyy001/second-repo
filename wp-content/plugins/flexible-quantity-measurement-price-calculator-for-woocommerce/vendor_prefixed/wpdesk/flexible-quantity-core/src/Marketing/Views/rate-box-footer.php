<?php

namespace WDFQVendorFree;

use WDFQVendorFree\WPDesk\Library\Marketing\RatePlugin\RateBox;
/**
 * @var RateBox $boxes
 */
$rate_box = $params['rate_box'] ?? \false;
if (!$rate_box) {
    return;
}
