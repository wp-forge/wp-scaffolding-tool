<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

/**
 * Class FilePresent
 */
class FilePresent extends AbstractCondition {

	/**
	 * Validate condition properties.
	 *
	 * @return $this
	 */
	public function validate() {
		if ( ! $this->has( 'file' ) ) {
			$this->error( "Condition 'file' is missing for type: '{$this->get('condition')}'" );
		}

		return $this;
	}

	/**
	 * Evaluate whether or not a file exists.
	 *
	 * @return bool
	 */
	public function evaluate() {
		return file_exists( $this->store->get( 'file' ) );
	}
}
