<?php

namespace WP_Forge\WP_Scaffolding_Tool\Templates;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;

/**
 * Class TemplateFinder
 */
class TemplateFinder {

	use CLIOutput, DependencyInjection, Filesystem;

	/**
	 * Fetch all available templates.
	 *
	 * @return string[] A list of full template paths, indexed by keys using the following pattern: "namespace:relative/path"
	 */
	public function all() {

		static $templates;

		if ( ! isset( $templates ) ) {

			$templates = array();

			$dirIterator    = new RecursiveDirectoryIterator( $this->container( 'template_dir' ), RecursiveDirectoryIterator::FOLLOW_SYMLINKS );
			$filterIterator = new TemplateFilterIterator( $dirIterator );
			$iterator       = new RecursiveIteratorIterator( $filterIterator, RecursiveIteratorIterator::SELF_FIRST );
			foreach ( $iterator as $item ) {

				$templatePath = $this->appendPath( $item->getPath(), $item->getFilename() );
				$config       = $this->appendPath( $templatePath, $this->container( 'template_config_filename' ) );

				if ( ! file_exists( $config ) ) {
					// No config file exists in this directory. Keep looking.
					continue;
				}

				$relativePath = trim(
					str_replace( $this->container( 'template_dir' ), '', $templatePath ),
					DIRECTORY_SEPARATOR
				);

				$namespace = $this->getPathSegment( $relativePath, 0, 'default' );
				$path      = str_replace( $namespace . DIRECTORY_SEPARATOR, '', $relativePath );

				$templates[ "{$namespace}:{$path}" ] = $templatePath;
			}
		}

		return $templates;
	}

	/**
	 * Get the keys for all available templates.
	 *
	 * @return string[]
	 */
	public function keys() {
		return array_keys( $this->all() );
	}

	/**
	 * Get a template's full path based on the relative path and namespace.
	 *
	 * @param string $path      Relative path
	 * @param string $namespace Namespace
	 *
	 * @return string
	 */
	public function get( $path, $namespace = 'default' ) {
		return $this->has( $path, $namespace ) ? $this->all()[ "{$namespace}:{$path}" ] : '';
	}

	/**
	 * Get available template namespaces.
	 *
	 * This is just a list of folder names in the ~/.wp-cli/templates directory.
	 *
	 * @return array
	 */
	public function getNamespaces() {
		$namespaces = array();
		$iterator   = new RecursiveDirectoryIterator(
			$this->container( 'template_dir' ),
			RecursiveDirectoryIterator::SKIP_DOTS
		);
		foreach ( $iterator as $item ) {
			$namespaces[] = $item->getFilename();
		}

		return $namespaces;
	}

	/**
	 * Check if a template exists.
	 *
	 * @param string $path      Relative path
	 * @param string $namespace Namespace
	 *
	 * @return bool
	 */
	public function has( $path, $namespace = 'default' ) {
		return array_key_exists( "{$namespace}:{$path}", $this->all() );
	}

	/**
	 * Find a template by path.
	 *
	 * @param string $path Relative template path
	 *
	 * @return array
	 */
	public function findByPath( $path ) {

		$foundTemplates = array();

		$templates  = $this->all();
		$namespaces = $this->getNamespaces();

		foreach ( $namespaces as $namespace ) {
			if ( $this->has( $path, $namespace ) ) {
				$foundTemplates[ "{$namespace}:{$path}" ] = $templates[ "{$namespace}:{$path}" ];
			}
		}

		return $foundTemplates;
	}

}
