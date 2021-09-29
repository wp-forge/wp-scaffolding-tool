<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

/**
 * Class Confirm
 */
class Confirm extends AbstractPrompt {

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		// Get the value from the user
		$this->value = $this->cli()->confirm( $this->message() )->confirmed();

		return $this;
	}

}
