<?php

namespace WP_Forge\WP_Scaffolding_Tool\Commands;

use WP_Forge\WP_Scaffolding_Tool\AbstractCommand;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Config;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Filesystem;

/**
 * Manage Git repositories containing templates for scaffolding.
 */
class RepoCommand extends AbstractCommand {

	use DependencyInjection, Config, Filesystem;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'repo';

	/**
	 * Clone a template repository.
	 *
	 * ## OPTIONS
	 *
	 * <repository_url>
	 * : The URL for the Git repository.
	 *
	 * [--as=<name>]
	 * : Optionally assign a name to this repository.
	 * ---
	 * default: default
	 * ---
	 *
	 * [--branch=<name>]
	 * : The branch to pull.
	 * ---
	 * default: master
	 * ---
	 *
	 * [--remote=<name>]
	 * : The remote name.
	 * ---
	 * default: origin
	 * ---
	 *
	 * [--force]
	 * : Whether or not to force override an existing repository.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function clone( $args, $options ) { // phpcs:ignore PHPCompatibility.Keywords.ForbiddenNames.cloneFound

		$this->init( $args, $options );

		// Ensure that git is available
		$this->gitCheck();

		$url  = $this->argument();
		$name = $this->option( 'as', 'default' );

		$path = $this->appendPath( $this->container( 'template_dir' ), $name );

		if ( file_exists( $path ) && ! $this->option( 'force', false ) ) {

			$this->error( 'Repository has already been cloned!', false );
			if ( ! $this->cli()->confirm( 'Do you want to overwrite the existing template files?' )->confirmed() ) {
				exit( 1 );
			}
		}

		if ( file_exists( $path ) ) {
			// Clean up directory
			$this->deleteRepo( $name );
		}

		$branch = $this->option( 'branch', 'master' );
		$remote = $this->option( 'remote', 'origin' );

		// Clone the repository
		$this->gitClone( $url, $path );
		$this->gitUpdate( $path, $branch, $remote );

		if ( 'default' === $name ) {
			// Sets the repo that should be auto-cloned in the event that the default templates are deleted
			$this->globalConfig()->data()->set( 'default_template_repo', $url );
		}

		$this->globalConfig()->data()->set( "templates.{$name}.url", $url );
		$this->globalConfig()->data()->set( "templates.{$name}.branch", $branch );
		$this->globalConfig()->data()->set( "templates.{$name}.remote", $remote );
		$this->globalConfig()->data()->set( "templates.{$name}.isSymlink", false );
		$this->globalConfig()->save();

	}

	/**
	 * Update a template repository.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name assigned to the repo.
	 * ---
	 * default: default
	 * ---
	 *
	 * [--branch=<name>]
	 * : The branch to pull.
	 * ---
	 * default: master
	 * ---
	 *
	 * [--remote=<name>]
	 * : The remote name.
	 * ---
	 * default: origin
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function update( $args, $options ) {

		$this->init( $args, $options );

		// Ensure that git is available
		$this->gitCheck();

		$name = $this->argument( 0, 'default' );

		$dir  = $this->container( 'template_dir' );
		$path = $this->appendPath( $dir, $name );

		if ( empty( $name ) || ! file_exists( $path ) ) {
			$this->error( "No repository found under the name '{$name}'!", false );
			$this->error( "Run 'wp {$this->getCommand()} clone' to clone a new repository." );
		}

		$branch = $this->option( 'branch', 'master' );
		$remote = $this->option( 'remote', 'origin' );

		// Pull the repository
		$this->gitUpdate( $path, $branch, $remote );

		$this->globalConfig()->data()->set( "templates.{$name}.branch", $branch );
		$this->globalConfig()->data()->set( "templates.{$name}.remote", $remote );
		$this->globalConfig()->save();

	}

	/**
	 * Delete a template repository.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the repo.
	 * ---
	 * default: default
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function delete( $args, $options ) {

		$this->init( $args, $options );

		$shouldDelete = $this->cli()->confirm( 'Are you sure you want to delete this repository?' )->confirmed();

		if ( $shouldDelete ) {

			$name = $this->argument( 0, 'default' );

			$this->deleteRepo( $name );

			$this->globalConfig()->data()->forget( "templates.{$name}" );
			$this->globalConfig()->save();

			$this->success( 'Repository deleted successfully!' );

		}

	}

	/**
	 * Register a symlink to a template set.
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : The path where your templates live.
	 * ---
	 * default: .
	 * ---
	 *
	 * [--as=<name>]
	 * : The name assigned to the template collection.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function link( $args, $options ) {

		$this->init( $args, $options );

		$path = realpath( $this->argument() );
		$name = $this->option( 'as', 'default' );

		if ( ! file_exists( $path ) ) {
			$this->error( 'Provided file path does not exist: ' . $path );
		}

		if ( ! is_dir( $path ) ) {
			$this->error( 'Templates must live in a directory!' );
		}

		if ( file_exists( $this->appendPath( $this->container( 'template_dir' ), $name ) ) ) {
			$this->error( 'Template directory already exists!' );
		}

		chdir( $this->container( 'template_dir' ) );

		if ( ! symlink( $path, $name ) ) {
			$this->error( 'Unable to create symlink!' );
		}

		$this->globalConfig()->data()->set( "templates.{$name}.path", $path );
		$this->globalConfig()->data()->set( "templates.{$name}.isSymlink", true );
		$this->globalConfig()->save();

		$this->success( 'Symlink created!' );
	}

	/**
	 * List the registered template repositories.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function list( $args, $options ) { // phpcs:ignore PHPCompatibility.Keywords.ForbiddenNames.listFound

		$this->init( $args, $options );

		$iterator = new \RecursiveDirectoryIterator( $this->appendPath( $this->container( 'home_dir' ), '.wp-cli', 'templates' ), \RecursiveDirectoryIterator::SKIP_DOTS );

		foreach ( $iterator as $item ) {

			// Get full path
			$path = $this->appendPath( $item->getPath(), $item->getFilename() );

			// Switch to directory
			chdir( $path );

			// Get the Git URL
			$remote = $this->globalConfig()->data()->get( "templates.{$item->getFilename()}.remote", 'origin' );
			$url    = trim( shell_exec( "git config --get remote.{$remote}.url" ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec

			// If path is a symlink, return that instead
			if ( is_link( $path ) ) {
				$url = readlink( $path );
			}

			$this->out( "<yellow>{$item->getFilename()}</yellow>: {$url}" );
		}
	}

	/**
	 * Delete a repo by name.
	 *
	 * @param string $name Name of the repo to be deleted
	 */
	protected function deleteRepo( $name ) {
		$dir  = $this->container( 'template_dir' );
		$path = $this->appendPath( $dir, $name );

		$this->cli()->blue( 'Deleting existing files located at: ' . $path );

		if ( is_link( $path ) ) {
			$this->filesystem( $dir )->delete( $name );
		} else {
			$this->filesystem( $dir )->deleteDirectory( $name );
		}
	}

	/**
	 * Check if Git is available.
	 */
	protected function gitCheck() {
		// Ensure that git is available
		exec( 'command -v git', $output, $result ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		if ( 0 !== $result ) {
			$this->error( 'Git is not installed!' );
		}
	}

	/**
	 * Clone a Git repository.
	 *
	 * @param string $url Git URL
	 * @param string $path Destination path
	 */
	protected function gitClone( $url, $path ) {
		shell_exec( "git clone {$url} {$path}" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
	}

	/**
	 * Update a Git repository.
	 *
	 * @param string $path Path to Git repository
	 * @param string $branch Git branch
	 * @param string $remote Git remote
	 */
	protected function gitUpdate( $path, $branch = 'master', $remote = 'origin' ) {
		shell_exec( "git -C {$path} pull {$remote} {$branch} --no-rebase" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
	}

}
