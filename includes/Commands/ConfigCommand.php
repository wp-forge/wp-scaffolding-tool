<?php

namespace WP_Forge\WP_Scaffolding_Tool\Commands;

use WP_CLI;
use WP_Forge\WP_Scaffolding_Tool\AbstractCommand;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Config;
use WP_Forge\WP_Scaffolding_Tool\GlobalConfig;
use WP_Forge\WP_Scaffolding_Tool\ProjectConfig;
use WP_Forge\WP_Scaffolding_Tool\Utilities;

use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\launch_editor_for_input;

/**
 * Manage project or global-level configuration files.
 */
class ConfigCommand extends AbstractCommand {

	use Config;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'config';

	/**
	 * Create a config file.
	 *
	 * ## OPTIONS
	 *
	 * [--global]
	 * : Create a global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function create( $args, $options ) {
		$this->init( $args, $options );
		$global = $this->option( 'global', false );
		if ( $global ) {
			if ( $this->globalConfig()->hasConfig() ) {
				$this->error( 'A global config file already exists!' );
			}
			$this->globalConfig()->save();
		} else {
			WP_CLI::RunCommand( $this->container->get( 'base_command' ) . ' init' );
		}
	}

	/**
	 * Launches system editor to edit the config file.
	 *
	 * ## OPTIONS
	 *
	 * [--global]
	 * : Edit the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function edit( $args, $options ) {
		$this->init( $args, $options );
		$config  = $this->getConfig();
		$content = launch_editor_for_input( $config->read(), $config->fileName(), '.json' );
		if ( false === $content ) {
			$this->warning( 'No changes made to ' . $config->fileName() . ', aborted.' );
		} else {
			// Write contents and attempt to parse.
			$config->write( $content )->parse();
		}
	}

	/**
	 * Check if the config file has a specific value. Dot notation can be used for nested values.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key to check.
	 *
	 * [--global]
	 * : Edit the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function has( $args, $options ) {
		$this->init( $args, $options );
		$key = $this->argument();
		$has = $this->getConfig()->data()->has( $key );
		$this->out( $has ? 'true' : 'false' );
	}

	/**
	 * Get a value from a config file. Dot notation can be used for nested values.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key to fetch.
	 *
	 * [--global]
	 * : Edit the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function get( $args, $options ) {
		$this->init( $args, $options );
		$value = $this->getConfig()->data()->get( $this->argument() );
		WP_CLI::print_value( $value );
	}

	/**
	 * Set a value in a config file. Dot notation can be used for nested values.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key to set.
	 *
	 * <value>
	 * : The value to set.
	 *
	 * [--global]
	 * : Edit the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function set( $args, $options ) {
		$this->init( $args, $options );
		$key    = $this->argument( 0 );
		$value  = $this->argument( 1 );
		$config = $this->getConfig();
		$config->data()->set( $key, $value );
		$config->save();
		$this->success( 'Value set successfully!' );
	}

	/**
	 * Delete a value from a config file. Dot notation can be used for nested values.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The key to delete.
	 *
	 * [--global]
	 * : Edit the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function delete( $args, $options ) {
		$this->init( $args, $options );
		$key    = $this->argument();
		$config = $this->getConfig();
		$config->data()->forget( $key );
		$config->save();
		$this->success( 'Value deleted successfully!' );
	}

	/**
	 * List the settings from a config file.
	 *
	 * ## OPTIONS
	 *
	 * [--global]
	 * : List settings from the global config file.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: json
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @subcommand list
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function list_( $args, $options ) {
		$this->init( $args, $options );
		$format = $this->option( 'format', 'json' );

		$config = $this->getConfig();

		if ( 'json' === $format ) {
			$this->out( $config->data()->toJson() );
			exit;
		}

		// TODO: Get this to work!
		$data = Utilities::flattenArray( $this->getConfig()->data()->toArray() );
		format_items( $format, $data, array_keys( $data ) );
	}

	/**
	 * Gets the path to a config file.
	 *
	 * ## OPTIONS
	 *
	 * [--global]
	 * : Get the path for the global config file.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function path( $args, $options ) {
		$this->init( $args, $options );
		$this->out( $this->getConfig()->path() );
	}

	/**
	 * Get the appropriate config instance.
	 *
	 * @return GlobalConfig|ProjectConfig
	 */
	protected function getConfig() {
		$global = $this->option( 'global', false );
		return $global ? $this->globalConfig() : $this->projectConfig();
	}


}
