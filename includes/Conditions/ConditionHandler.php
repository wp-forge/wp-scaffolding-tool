<?php

namespace WP_Forge\WP_Scaffolding_Tool\Conditions;

use WP_Forge\WP_Scaffolding_Tool\Concerns\CLIOutput;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class ConditionHandler
 */
class ConditionHandler {

	use CLIOutput;

	/**
	 * A collection of conditions.
	 *
	 * @var AbstractCondition[]
	 */
	protected $conditions = array();

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
	 * Add a condition.
	 *
	 * @param array $args Condition arguments
	 *
	 * @return $this
	 */
	public function add( array $args ) {
		/**
		 * Condition instance
		 *
		 * @var AbstractCondition $condition
		 */
		$condition = $this->container->get( 'condition' )( $args );
		$condition->withData( $this->store );
		array_push( $this->conditions, $condition );

		return $this;
	}

	/**
	 * Set all conditions.
	 *
	 * @param array[] $conditions Collection of conditions.
	 *
	 * @return $this
	 */
	public function populate( array $conditions ) {
		$this->conditions = array();
		foreach ( $conditions as $args ) {
			$this->add( $args );
		}

		return $this;
	}

	/**
	 * Evaluate all conditions.
	 *
	 * @param string $relation Condition relationship
	 *
	 * @return bool
	 */
	public function evaluate( $relation = 'AND' ) {
		$result = 'AND' === $relation ? true : false;
		foreach ( $this->conditions as $condition ) {
			$value  = $condition->validate()->evaluate();
			$result = ( 'AND' === $relation ) ? $result && $value : $result || $value;
		}

		return $result;
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
