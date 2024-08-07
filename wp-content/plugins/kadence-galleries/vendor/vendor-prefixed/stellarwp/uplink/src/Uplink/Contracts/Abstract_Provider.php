<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 01-April-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace KadenceWP\KadenceGalleries\StellarWP\Uplink\Contracts;

use KadenceWP\KadenceGalleries\StellarWP\ContainerContract\ContainerInterface;
use KadenceWP\KadenceGalleries\StellarWP\Uplink\Config;

abstract class Abstract_Provider implements Provider_Interface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor for the class.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Config::get_container();
	}

}
