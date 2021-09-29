<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

/**
 * Class Boolean
 */
class Boolean extends AbstractPrompt {

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		$input = $this->cli()->confirm( $this->message() );

		$this->value = $input->confirmed() ? 'true' : 'false';

		return $this;
	}

}
