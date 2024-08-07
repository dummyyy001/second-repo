<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 26-January-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace KadenceWP\KadenceConversions\StellarWP\Uplink\Contracts;

use KadenceWP\KadenceConversions\StellarWP\ContainerContract\ContainerInterface;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Config;

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
