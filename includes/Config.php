<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class Config
 */
class Config {

	use CLIOutput, DependencyInjection, Filesystem;

	/**
	 * The config file name.
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * Configuration data.
	 *
	 * @var \WP_Forge\DataStore\DataStore
	 */
	protected $data;

	/**
	 * The path where the config file was found.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Config constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->data      = new DataStore();
	}

	/**
	 * Set the configuration data.
	 *
	 * @param array|DataStore $data Config data
	 *
	 * @return $this
	 */
	public function withData( $data ) {
		if ( is_array( $data ) ) {
			$this->data->reset( $data );
		}
		if ( is_a( $data, DataStore::class ) ) {
			$this->data = $data;
		}

		return $this;
	}

	/**
	 * Set the config filename.
	 *
	 * @param string $fileName Name of config file
	 *
	 * @return $this
	 */
	public function withFileName( $fileName ) {
		$this->fileName = $fileName;

		return $this;
	}

	/**
	 * Set the path.
	 *
	 * @param string $path Path to config file
	 *
	 * @return $this
	 */
	public function withPath( $path ) {
		$this->path = $path;

		return $this;
	}

	/**
	 * Get the config data store.
	 *
	 * @return \WP_Forge\DataStore\DataStore
	 */
	public function data() {
		return $this->data;
	}

	/**
	 * Get the directory where the config is located.
	 *
	 * @return string
	 */
	public function path() {
		return $this->path;
	}

	/**
	 * Get the config file name.
	 *
	 * @return string
	 */
	public function fileName() {
		return $this->fileName;
	}

	/**
	 * Get the config file path.
	 *
	 * @return string
	 */
	public function filePath() {
		return $this->appendPath( $this->path, $this->fileName );
	}

	/**
	 * Check if a configuration file exists.
	 *
	 * @param string $path Path to check for a config file.
	 *
	 * @return bool
	 */
	public function hasConfig( $path = null ) {
		$path = is_null( $path ) ? $this->path : $path;

		return file_exists( $this->appendPath( $path, $this->fileName ) );
	}

	/**
	 * Parse the configuration file.
	 *
	 * @return array
	 */
	public function parse() {
		$content = $this->read();
		$config  = json_decode( $content, true );
		if ( is_null( $config ) ) {
			$filePath = $this->appendPath( $this->path, $this->fileName );
			$this->error( 'Unable to parse configuration file: ' . $filePath );
		}
		$this->data->put( $config );

		return $config;
	}

	/**
	 * Read the config file.
	 *
	 * @return string
	 */
	public function read() {
		return $this->filesystem( $this->path )->read( $this->fileName );
	}

	/**
	 * Write content to file.
	 *
	 * @param string $content Content to be written to the file
	 *
	 * @return $this
	 */
	public function write( $content ) {
		$this->filesystem( $this->path )->write( $this->fileName, $content );

		return $this;
	}

	/**
	 * Save the configuration file.
	 *
	 * @return $this
	 */
	public function save() {
		return $this->write( json_encode( (object) $this->data->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

}
