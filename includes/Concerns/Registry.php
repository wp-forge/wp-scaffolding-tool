<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

trait Registry {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get the Prompts class with available prompts.
	 *
	 * @return \WP_Forge\DataStore\DataStore
	 */
	protected function registry() {
		return $this->container->get( 'registry' );
	}

}
