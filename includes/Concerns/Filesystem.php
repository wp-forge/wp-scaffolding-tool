<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

/**
 * Trait Filesystem
 */
trait Filesystem {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get the Filesystem instance for a given path.
	 *
	 * @param string $path Base file path
	 *
	 * @return \League\Flysystem\Filesystem
	 */
	protected function filesystem( $path ) {
		return $this->container->get( 'filesystem' )( $path );
	}

	/**
	 * Safely append to a path.
	 *
	 * @param string $path   Path
	 * @param string $append Path to be appended
	 *
	 * @return string
	 */
	protected function appendPath( $path, $append ) {
		$args = func_get_args();
		array_shift( $args );
		$append = implode( DIRECTORY_SEPARATOR, array_filter( $args ) );

		return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $append;
	}

	/**
	 * Safely get a path segment by index.
	 *
	 * @param string $path    Path
	 * @param int    $index   Segment index
	 * @param mixed  $default Default value if segment doesn't exist
	 *
	 * @return string
	 */
	protected function getPathSegment( $path, $index = 0, $default = null ) {
		$segments = empty( $path ) ? array() : array_filter( explode( DIRECTORY_SEPARATOR, $path ) );
		if ( array_key_exists( $index, $segments ) ) {
			return $segments[ $index ];
		}

		return $default;
	}

}
