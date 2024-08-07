<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by kadencewp on 26-January-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */ declare( strict_types=1 );

namespace KadenceWP\KadenceConversions\StellarWP\Uplink\Resources\Filters;

use Countable;
use FilterIterator;
use KadenceWP\KadenceConversions\StellarWP\Uplink\Resources\Plugin;

/**
 * @method Plugin current()
 */
class Plugin_FilterIterator extends FilterIterator implements Countable {

	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$resource = $this->getInnerIterator()->current();

		return 'plugin' === $resource->get_type();
	}

	/**
	 * @inheritDoc
	 */
	public function count() : int {
		return iterator_count( $this );
	}

}
