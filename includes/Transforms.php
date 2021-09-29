<?php

namespace WP_Forge\WP_Scaffolding_Tool;

use WP_Forge\Helpers\Str;

/**
 * Class Transforms
 */
class Transforms {

	/**
	 * Abbreviate a string by getting the first letter of each word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function abbreviate( $value ) {
		$initials = array();
		$words    = explode( '-', Str::kebab( $value ) );
		foreach ( $words as $word ) {
			$initials[] = substr( $word, 0, 1 );
		}

		return implode( $initials );
	}

	/**
	 * Convert a string to camel case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function camelCase( $value ) {
		return Str::camel( $value );
	}

	/**
	 * Convert a string to dash case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function dashCase( $value ) {
		return self::kebabCase( $value );
	}

	/**
	 * Convert to dot case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function dotCase( $value ) {
		return str_replace( '-', '.', self::kebabCase( $value ) );
	}

	/**
	 * Convert a string to kebab case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function kebabCase( $value ) {
		return Str::kebab( $value );
	}

	/**
	 * Convert a string to lowercase.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function lowercase( $value ) {
		return Str::lower( $value );
	}

	/**
	 * Convert a string to pascal case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function pascalCase( $value ) {
		return Str::studly( $value );
	}

	/**
	 * Convert to path case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function pathCase( $value ) {
		return str_replace( '-', DIRECTORY_SEPARATOR, self::kebabCase( $value ) );
	}

	/**
	 * Get the plural form of a word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function plural( $value ) {
		return Str::plural( $value );
	}

	/**
	 * Get the singular form of a word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function singular( $value ) {
		return Str::singular( $value );
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function snakeCase( $value ) {
		return Str::snake( $value );
	}

	/**
	 * Convert a string to title case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function titleCase( $value ) {
		return Str::title( $value );
	}

	/**
	 * Convert a string to uppercase.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function uppercase( $value ) {
		return Str::upper( $value );
	}

	/**
	 * Convert string to proper words with spaces.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function words( $value ) {
		return implode( ' ', array_filter( preg_split( '/(?=[A-Z])/', Str::studly( $value ) ) ) );
	}

}
