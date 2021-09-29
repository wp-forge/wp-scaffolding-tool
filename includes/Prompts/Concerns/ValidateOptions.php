<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts\Concerns;

/**
 * Class ValidateOptions
 */
trait ValidateOptions {

	/**
	 * Validate the prompt's options.
	 */
	protected function validateOptions() {
		if ( ! $this->has( 'options' ) ) {
			$this->error( "Options missing for {$this->type()} prompt: {$this->name()})" );
		}
		$options = $this->get( 'options' );
		if ( empty( $options ) || ! is_array( $options ) ) {
			$this->error( "Invalid options provided for {$this->type()} prompt: {$this->name()}" );
		}
	}

}
