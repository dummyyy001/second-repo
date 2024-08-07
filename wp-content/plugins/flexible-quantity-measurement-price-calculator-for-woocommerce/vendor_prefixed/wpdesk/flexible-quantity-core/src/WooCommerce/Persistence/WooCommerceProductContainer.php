<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce\Persistence;

use WC_Product;
use WDFQVendorFree\WPDesk\Persistence\ElementNotExistsException;
use WDFQVendorFree\WPDesk\Persistence\FallbackFromGetTrait;
use WDFQVendorFree\WPDesk\Persistence\PersistentContainer;
/**
 * Can store data using WooCommerce metadata.
 * Warning: stored string '' is considered unset.
 *
 * @package WPDesk\Persistence\Wordpress
 */
final class WooCommerceProductContainer implements \WDFQVendorFree\WPDesk\Persistence\PersistentContainer
{
    use FallbackFromGetTrait;
    /** @var WC_Product */
    private $product;
    public function __construct(int $product_id)
    {
        $product = \wc_get_product($product_id);
        if (!\is_object($product)) {
            throw new \WDFQVendorFree\WPDesk\Persistence\ElementNotExistsException(\sprintf('Product %s not exists!', $product_id));
        }
        $this->product = $product;
    }
    public function set(string $key, $value)
    {
        if ($value !== null) {
            $this->product->update_meta_data($key, $value);
            $this->product->save();
        } else {
            $this->delete($key);
        }
    }
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \WDFQVendorFree\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $key));
        }
        return $this->product->get_meta($key);
    }
    public function has($id) : bool
    {
        $meta = $this->product->get_meta($id, \false);
        return \count($meta) !== 0;
    }
    public function delete(string $key)
    {
        $this->product->delete_meta_data($key);
        $this->product->save();
    }
}
