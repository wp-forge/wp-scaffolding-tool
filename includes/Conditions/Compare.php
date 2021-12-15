<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

/**
 * Class Compare
 */
class Compare extends AbstractCondition {

	/**
	 * Validate condition properties.
	 *
	 * @return $this
	 */
	public function validate() {
		if ( ! $this->has( 'key' ) ) {
			$this->error( "Condition 'key' is missing for type: '{$this->get('condition')}'" );
		}
		if ( ! $this->has( 'value' ) ) {
			$this->error( "Condition 'value' is missing for type: '{$this->get('condition')}'" );
		}
		if ( ! $this->store->has( $this->get( 'key' ) ) ) {
			$this->error( "Store did not contain: '{$this->get('key')}'" );
		}

		return $this;
	}

	/**
	 * Evaluate whether or not the key exists.
	 *
	 * @return bool
	 */
	public function evaluate() {
		return $this->compare();
	}

	/**
	 * Map comparison operators to logic.
	 *
	 * @return boolean
	 */
	private function compare() {
		$stored = $this->store->get( $this->get( 'key' ) );
		$value  = $this->get( 'value' );
		$type   = $this->has( 'compare' ) ? strtolower( $this->get( 'compare' ) ) : $this->default_comparison_operator( $stored );
		switch ( $type ) {
			case 'contains':
			case 'includes':
			case 'in':
				return $this->multitype_contains( $stored, $value );
			case 'notcontains':
			case 'notincludes':
			case 'notin':
			case 'nin':
				return ! $this->multitype_contains( $stored, $value );
			case '<':
			case 'lt':
				return $stored < $value;
			case '<=':
			case 'lte':
				return $stored <= $value;
			case '>':
			case 'gt':
				return $stored > $value;
			case '>=':
			case 'gte':
				return $stored >= $value;
			case '!=':
			case '!==':
			case 'ne':
			case 'notequals':
				return $stored !== $value;
			case '===':
			case '==':
			case '=':
			case 'eq':
			case 'equals':
			default:
				return $stored === $value;
		}
	}

	/**
	 * Detect needle within arrays, objects or strings.
	 *
	 * @param array|object|string|mixed $haystack Variable to search.
	 * @param string|mixed              $needle Variable to find in haystack.
	 * @return boolean
	 */
	private function multitype_contains( $haystack, $needle ) {
		if ( is_array( $haystack ) ) {
			return in_array( $needle, $haystack, true );
		} elseif ( is_object( $haystack ) ) {
			return property_exists( $haystack, $needle );
		} elseif ( is_string( $haystack ) ) {
			return stripos( $haystack, strtolower( $needle ) );
		}

		return false;
	}

	/**
	 * Set the default comparison operator condition when a config doesn't provide it.
	 *
	 * Arrays and objects check contains.
	 * Strings and otherwise check equals.
	 *
	 * @param mixed $stored Stored value in $this->store to type-check.
	 * @return string
	 */
	private function default_comparison_operator( $stored ) {
		if ( is_array( $stored ) || is_object( $stored ) ) {
			return 'in';
		}

		return 'eq';
	}
}
