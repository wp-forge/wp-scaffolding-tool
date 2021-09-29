<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

use League\CLImate\CLImate;

/**
 * Trait CLIOutput
 */
trait CLIOutput {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get the CLImate instance.
	 *
	 * @return CLImate
	 */
	protected function cli() {
		return $this->container->get( 'cli' );
	}

	/**
	 * Clear the terminal.
	 */
	protected function clear() {
		$this->cli()->clear();
	}

	/**
	 * Output a line of text.
	 *
	 * @param string $output Output
	 */
	protected function out( $output ) {
		$this->cli()->out( $output );
	}

	/**
	 * Output a line of text without a line break.
	 *
	 * @param string $output Output
	 */
	protected function inline( $output ) {
		$this->cli()->inline( $output );
	}

	/**
	 * Output an error message.
	 *
	 * @param string $output Output
	 * @param int    $exit Exit code
	 */
	protected function error( $output, $exit = 1 ) {
		$this->cli()->error( $output );
		if ( $exit ) {
			exit( $exit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Output a success message.
	 *
	 * @param string $output Output
	 */
	protected function success( $output ) {
		$this->cli()->info( $output );
	}

	/**
	 * Output a warning message.
	 *
	 * @param string $output Output
	 */
	protected function warning( $output ) {
		$this->cli()->yellow( $output );
	}

}
