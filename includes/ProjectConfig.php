<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use WP_Forge\Container\Container;
use WP_Forge\Helpers\Str;

/**
 * Class ProjectConfig
 */
class ProjectConfig extends Config {

	/**
	 * ProjectConfig constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		parent::__construct( $container );

		$this
			->withFileName( $container->get( 'project_config_filename' ) )
			->withPath( $this->findProjectRoot() );

		if ( $this->hasConfig() ) {
			$this->parse();
		}
	}

	/**
	 * Find the project root path.
	 *
	 * @return string
	 */
	protected function findProjectRoot() {

		// Start with the current directory
		$path = getcwd();

		// Get the home directory
		$homeDir = $this->container( 'home_dir' );

		// Search up the directory tree until we find a config or reach the home directory.
		while ( ! $this->hasConfig( $path ) && $path !== $homeDir ) {
			$path = Str::beforeLast( $path, DIRECTORY_SEPARATOR );
		}

		return $path === $homeDir ? getcwd() : $path;
	}

}
