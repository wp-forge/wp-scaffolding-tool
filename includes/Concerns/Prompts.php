<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

use WP_Forge\WP_Scaffolding_Tool\Prompts\PromptHandler;

trait Prompts {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Gets the PromptHandler class for configuring bulk prompts and managing the data.
	 *
	 * @return PromptHandler
	 */
	protected function prompts() {
		return new PromptHandler( $this->container );
	}

}
