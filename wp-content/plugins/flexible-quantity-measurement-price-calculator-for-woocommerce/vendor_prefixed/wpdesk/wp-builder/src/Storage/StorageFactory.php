<?php

namespace WDFQVendorFree\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \WDFQVendorFree\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
