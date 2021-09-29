<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use WP_Forge\Container\Container;

/**
 * Class GlobalConfig
 */
class GlobalConfig extends Config {

	/**
	 * ProjectConfig constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		parent::__construct( $container );

		$this
			->withFileName( $container->get( 'global_config_filename' ) )
			->withPath( $this->appendPath( $this->container( 'home_dir' ), '.wp-cli' ) );

		if ( $this->hasConfig() ) {
			$this->parse();
		}
	}

}
