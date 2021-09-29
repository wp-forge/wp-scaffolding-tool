<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class AbstractCondition
 */
abstract class AbstractCondition {

	use CLIOutput;

	/**
	 * Condition arguments
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Data store containing all prompt values.
	 *
	 * @var DataStore
	 */
	protected $store;

	/**
	 * Constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->store     = new DataStore();
	}

	/**
	 * Set arguments.
	 *
	 * @param array $args Prompt arguments
	 *
	 * @return $this
	 */
	public function withArgs( array $args ) {
		$this->args = $args;

		return $this;
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
	 * Check if a property exists.
	 *
	 * @param string $property Property name
	 *
	 * @return bool
	 */
	public function has( $property ) {
		return array_key_exists( $property, $this->args );
	}

	/**
	 * Get a property value.
	 *
	 * @param string $property Property name
	 * @param mixed  $default  Default value
	 *
	 * @return mixed
	 */
	public function get( $property, $default = null ) {
		return data_get( $this->args, $property, $default );
	}

	/**
	 * Set a property value.
	 *
	 * @param string $property Property name
	 * @param mixed  $value    Property value
	 *
	 * @return $this
	 */
	public function set( $property, $value ) {
		data_set( $this->args, $property, $value );

		return $this;
	}

	/**
	 * Get the data store.
	 *
	 * @return DataStore
	 */
	public function store() {
		return $this->store;
	}

	/**
	 * Get the data in the data store.
	 *
	 * @return array
	 */
	public function data() {
		return $this->store->toArray();
	}

	/**
	 * Validate the condition's arguments.
	 *
	 * @return $this
	 */
	abstract public function validate();

	/**
	 * Execute the condition and get the result.
	 *
	 * @return bool
	 */
	abstract public function evaluate();

}
