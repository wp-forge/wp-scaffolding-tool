<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

/**
 * Class Exists
 */
class Exists extends AbstractCondition {

	/**
	 * Validate condition properties.
	 *
	 * @return $this
	 */
	public function validate() {
		if ( ! $this->has( 'key' ) ) {
			$this->error( "Condition 'key' is missing for type: '{$this->get('condition')}'" );
		}

		return $this;
	}

	/**
	 * Evaluate whether or not the key exists.
	 *
	 * @return bool
	 */
	public function evaluate() {
		return $this->store->has( $this->get( 'key' ) );
	}
}
