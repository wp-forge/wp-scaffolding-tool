<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Mustache_Engine;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use WP_CLI;
use WP_Forge\WP_Scaffolding_Tool\Commands\RepoCommand;
use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Conditions\ConditionFactory;
use WP_Forge\WP_Scaffolding_Tool\Directives\DirectiveFactory;
use WP_Forge\WP_Scaffolding_Tool\Prompts\PromptFactory;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class Package
 */
class Package {

	use CLIOutput;

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Config constructor.
	 *
	 * @param array $args Arguments to be injected into the container.
	 */
	public function __construct( array $args = array() ) {
		$this->setup_container( $args );
		$this->configure();
		$this->registerCommands();
	}

	/**
	 * Do some configuration before registering the commands.
	 */
	public function configure() {

		register_shutdown_function( array( $this, 'onShutdown' ) );

		/**
		 * Get the global config.
		 *
		 * @var GlobalConfig $globalConfig
		 */
		$globalConfig = $this->container->get( 'global_config' );

		// If no default template repo exists in the global config, then set it (allows a user to set a new default).
		if ( ! $globalConfig->data()->has( 'default_template_repo' ) ) {
			$globalConfig->data()->set( 'default_template_repo', $this->container->get( 'default_template_repo' ) );
			$globalConfig->save();
		}

		// If there are no default templates, clone the default repo
		if ( ! file_exists( $this->getTemplatesDir() . DIRECTORY_SEPARATOR . 'default' ) ) {
			( new RepoCommand( $this->container ) )->clone( array( $globalConfig->data()->get( 'default_template_repo' ) ), array() );
		}

		// Allow the base command to be customized
		if ( $globalConfig->data()->has( 'base_command' ) ) {
			$this->container->set( 'base_command', $globalConfig->data()->get( 'base_command' ) );
		}

		/**
		 * Get the project config.
		 *
		 * @var ProjectConfig $projectConfig
		 */
		$projectConfig = $this->container->get( 'project_config' );

		// Get data store for collected user data
		$store = $this->container->get( 'store' );

		// Pre-populate user data with project settings
		$store->put( $projectConfig->data()->toArray() );

		// Also make important paths available
		$store->set( 'project_root', $projectConfig->hasConfig() ? $projectConfig->path() : getcwd() );
		$store->set( 'working_dir', getcwd() );

		// Set current year as an available value
		$store->set( 'year', gmdate( 'Y' ) );

		// Make the base command available
		$store->set( 'base_command', $this->container->get( 'base_command' ) );

	}

	/**
	 * Setup dependency injection container.
	 *
	 * @param array $args Arguments to be injected into the container.
	 */
	public function setup_container( array $args = array() ) {

		$container = new Container(
			array_merge(
				array(
					'home_dir'     => $this->getHomeDir(),
					'template_dir' => $this->getTemplatesDir(),
				),
				$args
			)
		);

		$container->set(
			'cli',
			$container->service(
				function () {
					return new CLImate();
				}
			)
		);

		$container->set(
			'mustache',
			$container->service(
				function () {

					// Get Mustache engine
					$mustache = new Mustache_Engine(
						array(
							'entity_flags' => ENT_QUOTES,
							'pragmas'      => array(
								Mustache_Engine::PRAGMA_FILTERS,
							),
						)
					);

					// Copy all transforms as helper methods
					$class   = new ReflectionClass( Transforms::class );
					$methods = $class->getMethods();
					foreach ( $methods as $method ) {
						$mustache->addHelper( $method->name, array( $method->class, $method->name ) );
					}

					return $mustache;
				}
			)
		);

		$container->set(
			'filesystem',
			$container->factory(
				function () {
					return function ( $path ) {
						return new Filesystem( new LocalFilesystemAdapter( $path ) );
					};
				}
			)
		);

		$container->set(
			'config',
			$container->factory(
				function ( Container $c ) {
					return new Config( $c );
				}
			)
		);

		$container->set(
			'project_config',
			$container->service(
				function ( Container $c ) {
					return new ProjectConfig( $c );
				}
			)
		);

		$container->set(
			'global_config',
			$container->service(
				function ( Container $c ) {
					return new GlobalConfig( $c );
				}
			)
		);

		$container->set(
			'registry',
			$container->service(
				function () {
					return new DataStore();
				}
			)
		);

		// Used to store all data collected from the user and persist across commands
		$container->set(
			'store',
			$container->service(
				function () {
					return new DataStore();
				}
			)
		);

		$container->set(
			'prompt',
			$container->factory(
				function ( Container $c ) {
					return function ( array $args ) use ( $c ) {
						return ( new PromptFactory( $c ) )->make( $args );
					};
				}
			)
		);

		$container->set(
			'directive',
			$container->factory(
				function ( Container $c ) {
					return function ( array $args ) use ( $c ) {
						return ( new DirectiveFactory( $c ) )->make( $args );
					};
				}
			)
		);

		$container->set(
			'condition',
			$container->factory(
				function ( Container $c ) {
					return function ( array $args ) use ( $c ) {
						return ( new ConditionFactory( $c ) )->make( $args );
					};
				}
			)
		);

		$this->container = $container;
	}

	/**
	 * Register WP CLI commands.
	 */
	public function registerCommands() {
		$iterator = new RecursiveDirectoryIterator( __DIR__ . '/Commands' );
		/**
		 * File instance.
		 *
		 * @var \SplFileInfo $file
		 */
		foreach ( new RecursiveIteratorIterator( $iterator ) as $file ) {
			if ( $file->getExtension() === 'php' ) {
				$relativePath      = str_replace( __DIR__ . DIRECTORY_SEPARATOR, '', $file->getPath() );
				$relativeNamespace = str_replace( DIRECTORY_SEPARATOR, '\\', $relativePath );
				$class             = __NAMESPACE__ . "\\$relativeNamespace\\" . $file->getBasename( '.php' );
				$instance          = new $class( $this->container );
				WP_CLI::add_command(
					$this->container->get( 'base_command' ) . ' ' . $class::COMMAND,
					$instance
				);
			}
		}
	}

	/**
	 * Shutdown callback.
	 */
	public function onShutdown() {
		// Display any registered messages
		$this->displayMessages();
	}

	/**
	 * Display messages.
	 */
	public function displayMessages() {
		$messages = $this->container->get( 'registry' )->get( 'messages' );
		if ( $messages && is_array( $messages ) ) {
			foreach ( $messages as $message ) {
				$type = data_get( $message, 'type' );
				if ( empty( $type ) || property_exists( $this, $type ) ) {
					$type = 'out';
				}
				$this->{$type}( data_get( $message, 'message' ), false );
			}
		}
	}

	/**
	 * Get the home directory.
	 *
	 * @return string
	 */
	public function getHomeDir() {
		$home = getenv( 'HOME' );
		if ( ! $home ) {
			// In Windows $HOME may not be defined
			$home = getenv( 'HOMEDRIVE' ) . getenv( 'HOMEPATH' );
		}

		return rtrim( $home, '/\\' );
	}

	/**
	 * Get the templates directory.
	 *
	 * @return string
	 */
	public function getTemplatesDir() {
		return implode( DIRECTORY_SEPARATOR, array( $this->getHomeDir(), '.wp-cli', 'templates' ) );
	}

}
