<?php

namespace WP_Forge\WP_Scaffolding_Tool\Commands;

use WP_Forge\WP_Scaffolding_Tool\AbstractCommand;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Config;
use WP_Forge\WP_Scaffolding_Tool\Concerns\DependencyInjection;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Store;
use WP_Forge\WP_Scaffolding_Tool\Utilities;

/**
 * Class InitCommand
 */
class InitCommand extends AbstractCommand {

	use DependencyInjection, Config, Store;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'init';

	/**
	 * Generates a project config file.
	 *
	 * ## OPTIONS
	 *
	 * [--force=<value>]
	 * : Whether or not to force overwrite files.
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args    Command arguments
	 * @param array $options Command options
	 */
	public function __invoke( $args, $options ) {

		$this->init( $args, $options );

		$force = $this->option( 'force', false );

		$project_root = $this
			->prompts()
			->add(
				array(
					'message'   => 'Project root path',
					'name'      => 'project_root',
					'type'      => 'input',
					'default'   => '.',
					'transform' => Utilities::class . '::resolvePath',
				)
			)
			->render()
			->store()
			->get( 'project_root' );

		$this->projectConfig()->withPath( $project_root );

		// Ensure that we aren't blindly overwriting an existing config
		if ( ! $force && $this->projectConfig()->hasConfig() ) {
			$this->error( 'A project config already exists at that location: ' . $this->projectConfig()->filePath(), false );
			if ( ! $this->cli()->confirm( 'Do you want to force overwrite?' )->confirmed() ) {
				exit( 1 );
			}
		}

		$data = $this
			->prompts()
			->populate(
				array(
					array(
						'message' => 'Project Name',
						'name'    => 'name',
					),
					array(
						'message' => 'Project Description',
						'name'    => 'description',
					),
					array(
						'message'  => 'Project URL',
						'name'     => 'url',
						'required' => false,
					),
					array(
						'message' => 'Vendor Name',
						'name'    => 'package.vendor',
					),
					array(
						'message'           => 'Package Name',
						'name'              => 'package.name',
						'default'           => '{{ name }}',
						'transform_default' => 'kebabCase',
					),
					array(
						'message' => 'License',
						'name'    => 'license.slug',
						'type'    => 'radio',
						'default' => 'GPL-2.0-or-later',
						'options' => array_keys( $this->getLicenses() ),
					),
					array(
						'message' => 'Author Name',
						'name'    => 'author.name',
						'type'    => 'input',
						'default' => trim( shell_exec( 'git config user.name' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
					),
					array(
						'message' => 'Author Email',
						'name'    => 'author.email',
						'type'    => 'input',
						'default' => trim( shell_exec( 'git config user.email' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
					),
					array(
						'message'  => 'Author URL',
						'name'     => 'author.url',
						'type'     => 'input',
						'required' => false,
					),
					array(
						'message' => 'Project text domain',
						'name'    => 'text_domain',
						'type'    => 'input',
						'default' => '{{ name | kebabCase }}',
					),
					array(
						'message'           => 'Project namespace',
						'name'              => 'namespace',
						'type'              => 'input',
						'default'           => '{{ name }}',
						'transform_default' => 'pascalCase',
					),
					array(
						'message'           => 'Long prefix',
						'name'              => 'prefixes.long',
						'type'              => 'input',
						'default'           => '{{ name }}',
						'transform_default' => 'pascalCase',
					),
					array(
						'message'           => 'Short prefix',
						'name'              => 'prefixes.short',
						'type'              => 'input',
						'default'           => '{{ name }}',
						'transform_default' => 'abbreviate',
					),
					array(
						'message'           => 'Function prefix',
						'name'              => 'prefixes.function',
						'type'              => 'input',
						'default'           => '{{ prefixes.short }}_',
						'transform_default' => 'lowercase',
					),
					array(
						'message'           => 'Constant prefix',
						'name'              => 'prefixes.constant',
						'type'              => 'input',
						'default'           => '{{ prefixes.short }}_',
						'transform_default' => 'uppercase',
					),
					array(
						'message'           => 'Meta prefix (used for post meta, options, etc.)',
						'name'              => 'prefixes.meta',
						'type'              => 'input',
						'default'           => '{{ prefixes.short }}_',
						'transform_default' => 'lowercase',
					),
					array(
						'message'           => 'Slug prefix (used for post types and taxonomy names)',
						'name'              => 'prefixes.slug',
						'type'              => 'input',
						'default'           => '{{ prefixes.short }}-',
						'transform_default' => 'lowercase',
					),
				)
			)
			->render()
			->data();

		// Set all license data
		$data['license']['name'] = $this->getLicenses()[ $data['license']['slug'] ]['name'];
		$data['license']['url']  = $this->getLicenses()[ $data['license']['slug'] ]['url'];

		$this
			->projectConfig()
			->withData( $data )
			->save();

		// Update all stored data to make it available for any subsequent commands
		foreach ( $this->projectConfig()->data()->toArray() as $key => $value ) {
			$this->store()->set( $key, $value );
		}

		// If the new project config is not in the current directory, ensure that subsequent commands run from the correct location.
		$this->store()->set( 'project_root', $this->projectConfig()->path() );
		$this->store()->set( 'working_dir', $this->projectConfig()->path() );
		chdir( $this->projectConfig()->path() );

		$this->success( 'Project config file created at: ' . $this->projectConfig()->path() );
	}

	/**
	 * Get available licenses.
	 *
	 * @return array
	 */
	public function getLicenses() {
		return array(
			'GPL-2.0-or-later' => array(
				'name' => 'GPL 2.0 or later',
				'url'  => 'https://www.gnu.org/licenses/gpl-2.0.html',
			),
		);
	}

}
