<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

use WP_CLI;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Conditions;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Store;
use WP_Forge\Helpers\Str;

/**
 * Class RunCommand
 */
class RunCommand extends AbstractDirective {

	use Conditions, Store;

	/**
	 * Command to be run.
	 *
	 * @var string
	 */
	protected $command;

	/**
	 * Conditions that must pass for command to be run.
	 *
	 * @var array
	 */
	protected $conditions = array();

	/**
	 * Directory from which to run command.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->command    = data_get( $args, 'command' );
		$this->conditions = data_get( $args, 'conditions' );
		$relativeTo       = data_get( $args, 'relativeTo', 'workingDir' );
		$this->path       = ( 'projectRoot' === $relativeTo ) ? $this->container->get( 'project_config' )->path() : getcwd();
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {
		if ( empty( $this->command ) ) {
			$this->error( 'Command is missing!' );
		}
		if ( ! empty( $this->conditions ) && ! is_array( $this->conditions ) ) {
			$this->error( 'Invalid conditions!' );
		}
	}

	/**
	 * Execute the directive.
	 */
	public function execute() {

		if ( $this->conditions ) {
			$shouldRun = $this
				->conditions()
				->withData( $this->store() )
				->populate( $this->conditions )
				->evaluate();

			if ( ! $shouldRun ) {
				return;
			}
		}

		// Allow for dynamic replacements in commands
		if ( false !== strpos( $this->command, '{{' ) ) {
			$this->command = $this->container->get( 'mustache' )->render( $this->command, $this->container->get( 'store' )->toArray() );
		}

		$this->success( 'Running ' . $this->command );

		if ( Str::startsWith( $this->command, array( 'wp', $this->container( 'base_command' ) ) ) ) {

			// Run a WP-CLI command
			WP_CLI::runcommand(
				Str::replaceFirst( 'wp ', '', $this->command ), // Remove 'wp' portion of command
				array(
					'launch' => false, // Use the existing process
					'force'  => $this->shouldOverwrite(),
				)
			);

		} else {

			passthru( $this->command, $code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_passthru
			if ( 0 !== $code ) {
				$this->error( 'Command failed: ' . $this->command );
			}
		}

	}

	/**
	 * Check if we should overwrite files.
	 *
	 * @return bool
	 */
	protected function shouldOverwrite() {
		return (bool) data_get( WP_CLI::get_runner()->assoc_args, 'force', false );
	}

}
