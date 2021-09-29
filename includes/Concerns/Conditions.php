<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

use WP_Forge\WP_Scaffolding_Tool\Conditions\ConditionHandler;

trait Conditions {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Gets the ConditionHandler class for bulk evaluation of conditions.
	 *
	 * @return ConditionHandler
	 */
	protected function conditions() {
		return new ConditionHandler( $this->container );
	}

}
