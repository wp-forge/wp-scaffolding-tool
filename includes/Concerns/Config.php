<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

trait Config {

	/**
	 * Dependency injection container
	 *
	 * @var /WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a new Config class instance.
	 *
	 * @return \WP_Forge\WP_Scaffolding_Tool\Config
	 */
	protected function config() {
		return $this->container->get( 'config' );
	}

	/**
	 * Get ProjectConfig class instance.
	 *
	 * @return \WP_Forge\WP_Scaffolding_Tool\ProjectConfig
	 */
	protected function projectConfig() {
		return $this->container->get( 'project_config' );
	}

	/**
	 * Get GlobalConfig class instance.
	 *
	 * @return \WP_Forge\WP_Scaffolding_Tool\GlobalConfig
	 */
	protected function globalConfig() {
		return $this->container->get( 'global_config' );
	}

}
