<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\Helpers\Str;

/**
 * Class DirectiveFactory
 */
class DirectiveFactory {

	use CLIOutput, DependencyInjection;

	/**
	 * Return a directive instance given the provided data.
	 *
	 * @param array $args Directive arguments.
	 *
	 * @return AbstractDirective
	 */
	public function make( array $args ) {

		$action = data_get( $args, 'action' );

		if ( empty( $action ) ) {
			$this->error( 'Directive action not provided!' );
		}

		if ( ! is_string( $action ) ) {
			$this->error( 'Directive action invalid!' );
		}

		$class = __NAMESPACE__ . '\\' . Str::studly( $action );
		if ( ! class_exists( $class ) ) {
			$this->error( "Directive action not found: {$action}" );
		}

		/**
		 * Get the directive instance.
		 *
		 * @var AbstractDirective $instance
		 */
		$instance = new $class( $this->container );

		if ( ! is_a( $instance, AbstractDirective::class ) ) {
			$class_name = get_class( $instance );
			$this->error( "Invalid directive class: {$class_name}" );
		}

		// Initialize properties
		$instance->initialize( $args );

		// Validate properties
		$instance->validate();

		return $instance;

	}

}
