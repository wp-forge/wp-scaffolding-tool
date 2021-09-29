<?php

namespace WP_Forge\WP_Scaffolding_Tool\Commands;

use WP_Forge\WP_Scaffolding_Tool\AbstractCommand;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Config;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Scaffolding;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Store;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Templates;
use WP_Forge\WP_Scaffolding_Tool\Directives\AbstractDirective;
use WP_Forge\WP_Scaffolding_Tool\Utilities;

/**
 * Class MakeCommand
 */
class MakeCommand extends AbstractCommand {

	use DependencyInjection, Config, Filesystem, Scaffolding, Store, Templates;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'make';

	/**
	 * Template config.
	 *
	 * @var \WP_Forge\WP_Scaffolding_Tool\Config
	 */
	protected $config;

	/**
	 * The template to be scaffolded.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Scaffold various code entities using the registered templates.
	 *
	 * ## OPTIONS
	 *
	 * <template>
	 * : The template name.
	 *
	 * [--force]
	 * : Whether or not to force overwrite files.
	 *
	 * [--project-root]
	 * : The path to consider the project root.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args    Command arguments
	 * @param array $options Command options
	 */
	public function __invoke( $args, $options ) {

		$this->init( $args, $options );

		// Set project root
		if ( $this->option( 'project-root' ) ) {
			$this->store()->set( 'project_root', Utilities::resolvePath( $this->option( 'project-root' ) ) );
		}

		$this->template = $this->setTemplate();
		$this->registry()->set( 'template', $this->template );

		$this->config = $this
			->config()
			->withFileName( $this->container( 'template_config_filename' ) )
			->withPath( $this->templatePath() );

		$this->validateTemplate();

		// Parse config
		$this->config->parse();

		// Make current template directory available to templates
		$this
			->store()
			->set(
				'template_dir',
				$this->appendPath( $this->container( 'template_dir' ), $this->template )
			);

		$this->collectData();

		$this->handleDirectives();

		$this->handleMessages();

	}

	/**
	 * Find a template.
	 *
	 * @return string
	 */
	protected function setTemplate() {

		$parts = explode( ':', $this->argument(), 2 );

		$path      = array_pop( $parts );
		$namespace = $this->registry()->get( 'namespace', data_get( $parts, '0', 'default' ) );

		if ( ! $this->registry()->has( 'namespace' ) ) {
			$this->registry()->set( 'namespace', $namespace );
		}

		$template = $this->templates()->get( $path, $namespace );

		if ( ! empty( $template ) ) {
			return $this->appendPath( $namespace, $path );
		}

		$this->warning( 'Unable to find template at: ' . $this->appendPath( $this->templatePath(), $namespace, $path ) );
		$this->warning( 'Attempting to locate template from another location...' );

		$templates = $this->templates()->findByPath( $path );

		foreach ( $templates as $key => $template ) {
			$parts     = explode( ':', $key, 2 );
			$namespace = array_shift( $parts );
			$this->success( 'Found template at: ' . $this->appendPath( $namespace, $path ) );
			if ( $this->cli()->confirm( 'Are you sure you want to use this template?' )->confirmed() ) {
				return $this->appendPath( $namespace, $path );
			} else {
				continue;
			}
		}

		$this->error( 'Unable to locate template!' );
	}

	/**
	 * Validate the template.
	 */
	protected function validateTemplate() {

		// Ensure user has provided a template
		if ( empty( $this->template ) ) {
			$this->error( 'Please provide a template!' );
		}

		$templatePath = $this->templatePath();

		// Ensure template path exists and is a directory
		if ( ! file_exists( $templatePath ) || ! is_dir( $templatePath ) ) {
			$this->error( 'Template does not exist!' );
		}

		// Ensure template config file exists
		if ( ! $this->config->hasConfig() ) {
			$this->error( 'Template config is missing!' );
		}
	}

	/**
	 * Collect any required data from the user.
	 */
	protected function collectData() {

		// Get prompts from the template config
		$prompts = $this->config->data()->get( 'prompts' );

		// Check if any prompts should be displayed
		if ( ! empty( $prompts ) && is_array( $prompts ) ) {
			$this
				->prompts()
				->withData( $this->store() )
				->populate( $prompts )
				->render();
		}
	}

	/**
	 * Handle scaffolding directives.
	 */
	protected function handleDirectives() {

		$directives = $this->config->data()->get( 'directives' );

		if ( $directives && is_array( $directives ) ) {

			foreach ( $directives as $args ) {

				/**
				 * Directive instance.
				 *
				 * @var AbstractDirective $instance
				 */
				$instance = $this->container( 'directive' )( $args );
				$instance->execute();

			}
		}

	}

	/**
	 * Handle messages.
	 */
	protected function handleMessages() {
		$messages = $this->config->data()->get( 'messages' );

		if ( ! empty( $messages ) && is_array( $messages ) ) {
			$this
				->registry()
				->set(
					'messages',
					array_merge(
						$this->registry()->get( 'messages', array() ),
						$messages
					)
				);
		}
	}

	/**
	 * Get the path to the directory containing the template(s) to be scaffolded.
	 *
	 * @return string
	 */
	protected function templatePath() {
		return $this->appendPath( $this->container( 'template_dir' ), $this->template );
	}

}
