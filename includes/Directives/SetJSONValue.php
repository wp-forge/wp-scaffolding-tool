<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Mustache;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Store;

/**
 * Class SetJSONValue
 */
class SetJSONValue extends AbstractDirective {

	use Filesystem, Mustache, Store;

	/**
	 * The filename.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * The path to the file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The key to set.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The value to set.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->file  = $this->replace( data_get( $args, 'file' ), $this->store()->toArray() );
		$this->path  = $this->replace( data_get( $args, 'path', getcwd() ), $this->store()->toArray() );
		$this->key   = $this->replace( data_get( $args, 'key' ), $this->store()->toArray() );
		$this->value = $this->replace( data_get( $args, 'value' ), $this->store()->toArray() );
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {
		if ( empty( $this->file ) ) {
			$this->error( 'JSON file name is missing!' );
		}
		if ( empty( $this->key ) ) {
			$this->error( 'JSON key is missing!' );
		}
		if ( empty( $this->value ) ) {
			$this->error( 'JSON value is missing!' );
		}
		$filepath = $this->appendPath( $this->path, $this->file );
		if ( ! file_exists( $filepath ) ) {
			$this->error( 'File does not exist: ' . $filepath );
		}
	}

	/**
	 * Execute the directive.
	 */
	public function execute() {
		$filesystem = $this->filesystem( $this->path );

		$data = json_decode( $filesystem->read( $this->file ), true );
		if ( ! $data ) {
			$this->error( 'Unable to decode JSON: ' . $this->appendPath( $this->path, $this->file ) );
		}

		$data = data_set( $data, $this->key, $this->value );

		$filesystem->write( $this->file, json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

}
