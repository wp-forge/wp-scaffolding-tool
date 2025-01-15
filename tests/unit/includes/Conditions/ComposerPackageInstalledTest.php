<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \WP_Forge\WP_Scaffolding_Tool\Conditions\ComposerPackageInstalled
 */
class ComposerPackageInstalledTest extends TestCase {

	/**
	 * @covers ::validate
	 */
	public function test_validate_not_configured(): void {
		$climate = new class() {
			public function error( $message ) {
				throw new \Exception( $message );
			}
		};

		$container = $this->createMock( \WP_Forge\Container\Container::class );
		$container->expects( $this->once() )
			->method( 'get' )
			->with( 'cli' )
			->willReturn( $climate );

		$args = array(
			'condition' => 'composerPackageInstalled',
		);

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		try {
			$sut->validate();
			$this->fail( 'Error not printed' );
		} catch ( \Exception $exception ) {
			$this->assertEquals( 'Condition \'package\' is missing for type: \'composerPackageInstalled\'', $exception->getMessage() );
		}
	}

	/**
	 * @covers ::validate
	 * @covers ::evaluate
	 */
	public function test_validate_no_composer_json(): void {
		$container = $this->createMock( \WP_Forge\Container\Container::class );

		$args = array(
			'condition' => 'composerPackageInstalled',
			'package'   => 'php-stubs/wordpress-stubs',
		);

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		$sut->validate();

		$result = $sut->evaluate();

		$this->assertFalse( $result );
	}

	/**
	 * @covers ::validate
	 * @covers ::evaluate
	 */
	public function test_validate_no_package_in_composer_json(): void {
		$container = $this->createMock( \WP_Forge\Container\Container::class );

		$args = array(
			'condition' => 'composerPackageInstalled',
			'package'   => 'php-stubs/wordpress-stubs',
		);

		$composerJson = <<<EOD
{
    "require": {
        "not/php-stubs": "*"
    }
}
EOD;
		$tempDir      = sys_get_temp_dir();
		file_put_contents( $tempDir . '/composer.json', $composerJson );
		chdir( $tempDir );

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		$sut->validate();

		$result = $sut->evaluate();

		$this->assertFalse( $result );

		unlink( $tempDir . '/composer.json' );
	}

	/**
	 * @covers ::validate
	 * @covers ::evaluate
	 */
	public function test_validate_package_in_require(): void {
		$container = $this->createMock( \WP_Forge\Container\Container::class );

		$args = array(
			'condition' => 'composerPackageInstalled',
			'package'   => 'php-stubs/wordpress-stubs',
		);

		$composerJson = <<<EOD
{
    "require": {
        "php-stubs/wordpress-stubs": "*"
    }
}
EOD;
		$tempDir      = sys_get_temp_dir();
		file_put_contents( $tempDir . '/composer.json', $composerJson );
		chdir( $tempDir );

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		$sut->validate();

		$result = $sut->evaluate();

		$this->assertTrue( $result );

		unlink( $tempDir . '/composer.json' );
	}

	/**
	 * @covers ::validate
	 * @covers ::evaluate
	 */
	public function test_validate_package_in_require_dev(): void {
		$container = $this->createMock( \WP_Forge\Container\Container::class );

		$args = array(
			'condition' => 'composerPackageInstalled',
			'package'   => 'php-stubs/wordpress-stubs',
		);

		$composerJson = <<<EOD
{
    "require-dev": {
        "php-stubs/wordpress-stubs": "*"
    }
}
EOD;
		$tempDir      = sys_get_temp_dir();
		file_put_contents( $tempDir . '/composer.json', $composerJson );
		chdir( $tempDir );

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		$sut->validate();

		$result = $sut->evaluate();

		$this->assertTrue( $result );

		unlink( $tempDir . '/composer.json' );
	}

	/**
	 * @covers ::validate
	 * @covers ::evaluate
	 */
	public function test_validate_package_in_composer_lock(): void {
		$container = $this->createMock( \WP_Forge\Container\Container::class );

		$args = array(
			'condition' => 'composerPackageInstalled',
			'package'   => 'php-stubs/wordpress-stubs',
		);

		$composerLockJson = <<<EOD
{
    "packages": [
        {
            "name": "not-php-stubs/wordpress-stubs"
        }
    ],
    "packages-dev": [
        {
            "name": "php-stubs/wordpress-stubs"
        }
    ]
}
EOD;
		$tempDir      = sys_get_temp_dir();
		file_put_contents( $tempDir . '/composer.json', '{}' );
		file_put_contents( $tempDir . '/composer.lock', $composerLockJson );
		chdir( $tempDir );

		$sut = ( new ComposerPackageInstalled( $container ) )->withArgs( $args );

		$sut->validate();

		$result = $sut->evaluate();

		$this->assertTrue( $result );

		unlink( $tempDir . '/composer.json' );
	}
}
