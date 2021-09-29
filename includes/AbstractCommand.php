<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Prompts;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Registry;

/**
 * Class AbstractCommand
 */
abstract class AbstractCommand {

	use DependencyInjection, CLIOutput, Prompts, Registry;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = '';

	/**
	 * CLI arguments.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * CLI options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Initialize command info.
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	protected function init( $args, $options ) {
		$this->args    = $args;
		$this->options = $options;
	}

	/**
	 * Get an argument by index.
	 *
	 * @param int   $index Argument index
	 * @param mixed $default Default value
	 *
	 * @return mixed
	 */
	protected function argument( $index = 0, $default = null ) {
		return data_get( $this->args, $index, $default );
	}

	/**
	 * Get an option by name, optionally set a default value.
	 *
	 * @param string $name Option name
	 * @param mixed  $default Default value
	 *
	 * @return mixed
	 */
	protected function option( $name, $default = null ) {
		return data_get( $this->options, $name, $default );
	}

	/**
	 * Get command.
	 *
	 * @return string
	 */
	protected function getCommand() {
		return $this->container( 'base_command' ) . ' ' . static::COMMAND;
	}

}
