<?php
/*
 * General utility functions not specific to Advanced Forms.
 */

/**
 * Resolves the value of a multi-dimensional array using dot notation.
 *
 * e.g; af_arr_get(['a' => ['b' => 1]], 'a.b') => 1
 *
 * @param array $array
 * @param string|array $key Dot-notated path to nested array value. If it is an array, items will be dot-notated. Can
 *      also just be a non-nested key.
 * @param null $default
 *
 * @return array|mixed|null
 */
function af_arr_get( $array, $key, $default = null ) {
	$current = $array;

	if ( is_array( $key ) ) {
		$key = join( '.', $key );
	}

	$p = strtok( $key, '.' );

	while ( $p !== false ) {
		if ( ! isset( $current[ $p ] ) ) {
			return $default;
		}
		$current = $current[ $p ];
		$p = strtok( '.' );
	}

	return $current;
}