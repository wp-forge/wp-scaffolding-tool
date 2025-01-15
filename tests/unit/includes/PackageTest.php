<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \WP_Forge\WP_Scaffolding_Tool\Package
 */
class PackageTest extends TestCase {

	/**
	 * @covers ::__construct
	 */
	public function test_construct() {
		$args = array(
			'global_config_filename'  => '.wp-forge.json',
			'default_template_repo'   => 'https://github.com/wp-forge/scaffolding-templates.git',
			'project_config_filename' => '.wp-forge-project.json',
			'base_command'            => 'forge',
		);

		$sut = new Package( $args );

		$this->assertInstanceOf( Package::class, $sut );
	}
}
