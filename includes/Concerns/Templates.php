<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

use WP_Forge\WP_Scaffolding_Tool\Templates\TemplateFinder;

/**
 * Trait Templates
 */
trait Templates {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a template finder instance.
	 *
	 * @return TemplateFinder
	 */
	protected function templates() {
		return new TemplateFinder( $this->container );
	}

}
