<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

use WP_Forge\WP_Scaffolding_Tool\Prompts\Concerns\ValidateOptions;

/**
 * Class Checkboxes
 */
class Checkboxes extends AbstractPrompt {

	use ValidateOptions;

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		// Get the value from the user
		$this->value = $this->cli()->checkboxes( $this->message(), $this->get( 'options' ) )->prompt();

		return $this;
	}

	/**
	 * Validate the prompt's arguments.
	 *
	 * @return $this
	 */
	public function validate() {
		parent::validate();
		$this->validateOptions();
		return $this;
	}

}
