<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

/**
 * Class CommandExists
 */
class CommandExists extends AbstractDirective {

	/**
	 * Path to file containing script.
	 *
	 * @var string
	 */
	protected $command;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->command = data_get( $args, 'command' );
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {
		if ( empty( $this->command ) ) {
			$this->error( 'Command name is missing!' );
		}
	}

	/**
	 * Execute the directive.
	 */
	public function execute() {

		// Ensure that a command is available
		exec( 'command -v ' . $this->command, $output, $result ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		if ( 0 !== $result ) {
			$this->error( "The {$this->command} command is not available!" );
		}

	}

}
