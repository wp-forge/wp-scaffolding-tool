<?php

namespace WP_Forge\WP_Scaffolding_Tool\Concerns;

/**
 * Trait Mustache
 */
trait Mustache {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Replace placeholders in content with actual data, if provided and required.
	 *
	 * @param string $content Content where placeholders will be replaced
	 * @param array  $data Data used for replacements
	 *
	 * @return string
	 */
	public function replace( $content, array $data ) {
		if ( ! empty( $data ) && is_string( $content ) && false !== strpos( $content, '{{' ) ) {
			$content = $this->mustache()->render( $content, $data );
		}
		return $content;
	}

	/**
	 * Get the Mustache_Engine instance.
	 *
	 * @return \Mustache_Engine
	 */
	protected function mustache() {
		return $this->container->get( 'mustache' );
	}

}
