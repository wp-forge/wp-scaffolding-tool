<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

class ComposerPackageInstalled extends AbstractCondition {

	/**
	 * Validate condition properties.
	 *
	 * @return $this
	 */
	public function validate() {
		if ( ! $this->has( 'package' ) ) {
			$this->error( "Condition 'package' is missing for type: '{$this->get('condition')}'" );
		}

		return $this;
	}

	/**
	 * Evaluate whether or not a package is required in Composer
	 *
	 * @return bool
	 */
	public function evaluate() {
		if ( ! file_exists( 'composer.json' ) ) {
			return false;
		}

		$composer = json_decode( file_get_contents( 'composer.json' ), true );
		if ( isset( $composer['require'][ $this->get('package') ] )
			|| isset( $composer['require-dev'][ $this->get('package') ] ) ) {
			return true;
		}

		if ( ! file_exists( 'composer.lock' ) ) {
			return false;
		}

		$composerLock = json_decode( file_get_contents( 'composer.lock' ), true );

		return in_array(
			$this->get('package'),
			array_map(
				function ( $package ) {
					return $package['name'];
				},
				array_merge( $composerLock['packages'], $composerLock['packages-dev'] )
			)
		);
	}
}
