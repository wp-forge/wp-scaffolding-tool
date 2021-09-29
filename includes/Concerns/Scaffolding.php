<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

use WP_Forge\WP_Scaffolding_Tool\Scaffold;

trait Scaffolding {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a new Scaffold class instance.
	 *
	 * @return \WP_Forge\WP_Scaffolding_Tool\Scaffold
	 */
	protected function scaffold() {
		return new Scaffold( $this->container );
	}

}
