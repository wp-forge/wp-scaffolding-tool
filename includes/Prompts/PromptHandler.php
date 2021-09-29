<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Mustache;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class PromptHandler
 */
class PromptHandler {

	use CLIOutput, Mustache;

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Data collected from the user.
	 *
	 * @var DataStore
	 */
	protected $store;

	/**
	 * Prompts
	 *
	 * @var \WP_Forge\WP_Scaffolding_Tool\Prompts\AbstractPrompt[]
	 */
	protected $prompts = array();

	/**
	 * PromptHandler constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->store     = new DataStore();
	}

	/**
	 * Set data.
	 *
	 * @param DataStore|array $data Collection of data
	 *
	 * @return $this
	 */
	public function withData( $data ) {
		if ( is_array( $data ) ) {
			$this->store->reset( $data );
		}
		if ( is_a( $data, DataStore::class ) ) {
			$this->store = $data;
		}

		return $this;
	}

	/**
	 * Add a prompt.
	 *
	 * @param array $args Prompt arguments
	 */
	public function add( array $args ) {
		/**
		 * Prompt instance
		 *
		 * @var \WP_Forge\WP_Scaffolding_Tool\Prompts\AbstractPrompt $prompt
		 */
		$prompt = $this->container->get( 'prompt' )( $args );
		$prompt->withData( $this->store );
		array_push( $this->prompts, $prompt );

		return $this;
	}

	/**
	 * Set all prompts.
	 *
	 * @param array[] $prompts Collection of prompts
	 */
	public function populate( array $prompts ) {
		$this->prompts = array();
		foreach ( $prompts as $args ) {
			$this->add( $args );
		}

		return $this;
	}

	/**
	 * Render prompts and persist to data store
	 *
	 * @return $this
	 */
	public function render() {
		foreach ( $this->prompts as $prompt ) {
			if ( ! $prompt->shouldRender() ) {
				continue;
			}
			$prompt->render()->transform()->save();
		}

		return $this;
	}

	/**
	 * Get all data.
	 *
	 * @return array
	 */
	public function data() {
		return $this->store->toArray();
	}

	/**
	 * Get data store.
	 *
	 * @return DataStore
	 */
	public function store() {
		return $this->store;
	}

}
