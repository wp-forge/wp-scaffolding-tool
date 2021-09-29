<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class Utilities
 */
class Utilities {

	/**
	 * Flatten an array using dot notation.
	 *
	 * @param array $array Array data
	 *
	 * @return array
	 */
	public static function flattenArray( array $array ) {
		$iterator = new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) );
		$result   = array();
		foreach ( $iterator as $value ) {
			$keys = array();
			foreach ( range( 0, $iterator->getDepth() ) as $depth ) {
				$keys[] = $iterator->getSubIterator( $depth )->key();
			}
			$result[ implode( '.', $keys ) ] = $value;
		}

		return $result;
	}

	/**
	 * Transform a value via a callable.
	 *
	 * @param mixed    $value    Value to be transformed
	 * @param callable $callable Callable to be called.
	 *
	 * @return mixed
	 */
	public static function transform( $value, $callable ) {
		// Check for registered transforms, otherwise look for a valid callback
		if ( method_exists( Transforms::class, $callable ) ) {
			return call_user_func( array( Transforms::class, $callable ), $value );
		}

		return $callable( $value );
	}

	/**
	 * Apply one or more transforms to a value, in order.
	 *
	 * @param mixed          $value      Value to be transformed
	 * @param array|callable $transforms Transforms to be called.
	 *
	 * @return mixed
	 */
	public static function applyTransforms( $value, $transforms ) {
		if ( is_string( $transforms ) ) {
			$transforms = (array) $transforms;
		}
		if ( ! empty( $transforms ) && is_array( $transforms ) ) {
			foreach ( $transforms as $callable ) {
				$value = self::transform( $value, $callable );
			}
		}

		return $value;
	}

	/**
	 * Resolve a path. Like realpath(), but works for non-existent files and directories.
	 *
	 * @param string $path Path to be resolved
	 *
	 * @return false|mixed|string
	 */
	public static function resolvePath( $path ) {
		if ( DIRECTORY_SEPARATOR !== '/' ) {
			$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );
		}
		$search = explode( '/', $path );
		$search = array_filter(
			$search,
			function ( $part ) {
				return '.' !== $part;
			}
		);
		$append = array();
		$match  = false;
		while ( count( $search ) > 0 ) { // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
			$match = realpath( implode( '/', $search ) );
			if ( false !== $match ) {
				break;
			}
			array_unshift( $append, array_pop( $search ) );
		};
		if ( false === $match ) {
			$match = getcwd();
		}
		if ( count( $append ) > 0 ) {
			$match .= DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $append );
		}

		return $match;
	}

}
