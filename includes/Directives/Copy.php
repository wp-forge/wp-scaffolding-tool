<?php

namespace WP_Forge\WP_Scaffolding_Tool\Directives;

use WP_CLI;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Config;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Mustache;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Registry;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Scaffolding;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Store;

/**
 * Class Copy
 */
class Copy extends AbstractDirective {

	use Config, Filesystem, Mustache, Registry, Scaffolding, Store;

	/**
	 * Type of copy action. Can be copyDir or copyFile.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * File or directory path relative to the source path.
	 *
	 * @var string
	 */
	protected $from;

	/**
	 * File or directory path relative to the target directory.
	 *
	 * @var string
	 */
	protected $to;

	/**
	 * The full path to the directory containing files to be copied.
	 *
	 * @var string
	 */
	protected $sourceDir;

	/**
	 * The full path to the directory into which files will be copied.
	 *
	 * @var string
	 */
	protected $targetDir;

	/**
	 * Data to be used for template replacements.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * A list of file paths to be excluded.
	 *
	 * @var string[]
	 */
	protected $exclusions = array();

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->from       = data_get( $args, 'from' );
		$this->to         = $this->replace( data_get( $args, 'to' ), $this->store()->toArray() );
		$this->targetDir  = data_get( $args, 'relativeTo' ) === 'projectRoot' ? $this->store()->get( 'project_root' ) : getcwd();
		$this->sourceDir  = $this->appendPath( $this->container( 'template_dir' ), $this->registry()->get( 'template' ) );
		$this->action     = is_dir( $this->appendPath( $this->sourceDir, $this->from ) ) ? 'copyDir' : 'copyFile';
		$this->data       = $this->store()->toArray();
		$this->exclusions = data_get( $args, 'exclude' );

		// Always exclude scaffolding template config files
		$this->exclusions[] = $this->container( 'template_config_filename' );
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {

		if ( empty( $this->from ) ) {
			$this->error( 'Source path is missing!' );
		}

		if ( empty( $this->to ) ) {
			$this->error( 'Target path is missing!' );
		}

		if ( ! file_exists( $this->appendPath( $this->sourceDir, $this->from ) ) ) {
			$this->error( "Source path is invalid: {$this->from}" );
		}

	}

	/**
	 * Execute the directive.
	 */
	public function execute() {

		// Copy file(s)
		$this
			->scaffold()
			->withSourceDir( $this->sourceDir )
			->withTargetDir( $this->targetDir )
			->overwrite( $this->shouldOverwrite() )
			->exclude( $this->exclusions )
			->{$this->action}( $this->from, $this->to, $this->data );
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
