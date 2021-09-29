<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;

/**
 * Class AbstractDirective
 */
abstract class AbstractDirective {

	use CLIOutput, DependencyInjection;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	abstract public function initialize( array $args );

	/**
	 * Validate the directive properties.
	 */
	abstract public function validate();

	/**
	 * Execute the directive.
	 */
	abstract public function execute();

}
