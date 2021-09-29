<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

/**
 * Class Password
 */
class Password extends AbstractPrompt {

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		// Get the value from the user
		$this->value = $this->cli()->password( $this->message() )->prompt();

		return $this;
	}

}
