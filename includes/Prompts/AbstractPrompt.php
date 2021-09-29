<?php

namespace WP_Forge\WP_Scaffolding_Tool\Prompts;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Conditions;
use WP_Forge\WP_Scaffolding_Tool\Concerns\Mustache;
use WP_Forge\WP_Scaffolding_Tool\Utilities;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class AbstractPrompt
 */
abstract class AbstractPrompt {

	use CLIOutput, Conditions, Mustache;

	/**
	 * Prompt arguments.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Dependency injection container
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
	 * The user-provided value.
	 *
	 * @var mixed
	 */
	protected $value;

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
	 * Get the prompt message.
	 *
	 * @return string
	 */
	public function message() {
		return (string) data_get( $this->args, 'message', '' );
	}

	/**
	 * Get the prompt name.
	 *
	 * @return string
	 */
	public function name() {
		return (string) data_get( $this->args, 'name', '' );
	}

	/**
	 * Get the prompt type.
	 *
	 * @return string
	 */
	public function type() {
		return strtolower( str_replace( __NAMESPACE__ . '\\', '', get_class( $this ) ) );
	}

	/**
	 * Get the value.
	 *
	 * @return mixed
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * Persist the value to the data store.
	 *
	 * @return $this
	 */
	public function save() {
		$this->store->set( $this->name(), $this->value );

		return $this;
	}

	/**
	 * Validate the prompt's arguments.
	 *
	 * @return $this
	 */
	public function validate() {
		if ( ! $this->has( 'name' ) ) {
			$this->error( "Prompt name is missing for type '{$this->type()}'" );
		}
		if ( empty( $this->name() ) ) {
			$this->error( "Prompt name is empty for type '{$this->type()}'" );
		}
		if ( ! $this->has( 'message' ) ) {
			$this->error( "Prompt message is missing for '{$this->name()}'" );
		}
		if ( empty( $this->message() ) ) {
			$this->error( "Prompt message is empty for '{$this->name()}'" );
		}

		return $this;
	}

	/**
	 * Transform the resulting value, if necessary.
	 *
	 * @return $this
	 */
	public function transform() {
		$this->value = Utilities::applyTransforms( $this->value, data_get( $this->args, 'transform' ) );

		return $this;
	}

	/**
	 * Render a prompt.
	 *
	 * @return $this
	 */
	abstract public function render();

	/**
	 * Check if we should render the field.
	 *
	 * @return bool
	 */
	public function shouldRender() {

		$shouldRender = true;

		// Don't render a field if a value already exists for a given name!
		if ( $this->store->has( $this->name() ) ) {
			$shouldRender = false;
		}

		if ( $shouldRender && $this->has( 'showIf' ) && is_array( $this->get( 'showIf' ) ) ) {
			$shouldRender = $this
				->conditions()
				->withData( $this->store )
				->populate( $this->get( 'showIf' ) )
				->evaluate();
		}

		return $shouldRender;
	}

}
