<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

use WP_Forge\WP_Scaffolding_Tool\Utilities;

/**
 * Class Input
 */
class Input extends AbstractPrompt {

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		$this->handleDefault();
		$this->updateMessage();

		$input = $this->cli()->input( $this->message() );

		// Set default value, if provided
		if ( $this->has( 'default' ) ) {
			$input->defaultTo( $this->get( 'default' ) );
		}

		// Get the value from the user
		$value = $input->prompt();

		// Continue to show prompt if value is empty and field is required.
		while ( empty( $value ) && $this->isRequired() ) {
			$this->error( 'Field is required!', 0 );
			$value = $input->prompt();
		}

		$this->value = $value;

		return $this;
	}

	/**
	 * Check if field is required.
	 *
	 * @return bool
	 */
	protected function isRequired() {
		$isRequired = true;
		if ( $this->has( 'required' ) ) {
			$isRequired = (bool) $this->get( 'required' );
		}

		return $isRequired;
	}

	/**
	 * Handle the default value, if provided.
	 */
	protected function handleDefault() {

		// Check if a default value is provided
		if ( $this->has( 'default' ) ) {

			$default = $this->get( 'default' );

			// Perform a replacement on the default value, if necessary
			$default = $this->replace( $default, $this->data() );

			// Transform the default value, if necessary
			if ( $this->has( 'transform_default' ) ) {
				$default = Utilities::applyTransforms( $default, $this->get( 'transform_default' ) );
			}

			// Update the default value
			$this->set( 'default', $default );

		}
	}

	/**
	 * Update the message to reflect a default value, if provided.
	 */
	protected function updateMessage() {
		$message = rtrim( $this->message(), ':?' );

		// Display default value, if provided
		if ( $this->has( 'default' ) ) {
			$message .= ' [<yellow>' . $this->get( 'default' ) . '</yellow>]';
		}

		$this->set( 'message', $message . ':' );
	}

}
