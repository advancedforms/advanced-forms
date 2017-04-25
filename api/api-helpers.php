<?php
	

/**
 * Returns true if a successful submission was performed
 *
 * @since 1.1
 */
function af_has_submission() {
	
	return ! is_null( AF()->submission );
	
}


/**
 * Searches input for tags {field:FIELD_NAME} and replaces with field values
 *
 * @since 1.0.1
 *
 */
function af_resolve_field_includes( $input, $fields = false ) {
	
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		
		$fields = AF()->submission['fields'];
		
	}
	
	
	if ( preg_match_all( "/{field:(.*?)}/", $input, $matches ) ) {
		
		foreach ($matches[1] as $i => $field) {
			
			$field_value = af_get_field( $field, $fields );
			
			if ( is_array( $field_value ) ) {
				$include_value = join( ', ', $field_value );
			} else {
				$include_value = (string)$field_value;
			}
			
			$input = str_replace( $matches[0][$i], $include_value, $input );
			
		}
		
	}
	
	return $input;
	
}


/**
 * Checks if the passed key is a valid form key (begins with form_)
 *
 * @since 1.0.1
 *
 */
function af_is_valid_form_key( $key ) {
	
	if ( ! is_string( $key ) ) {
		
		return false;
		
	}
	
	
	if ( 'form_' == substr( $key, 0, 5 ) ) {
		
		return true;
		
	}
	
	
	return false;
	
}


/**
 * Output an "Insert field" button populated with $fields
 * $floating adds class "floating" to the wrapper making the button float right in an input field
 *
 * @since 1.1.1
 *
 */
function _af_field_inserter_button( $fields, $floating = false ) {
	
	$classses = ( $floating ) ? 'floating' : '';
	
	echo '<a class="af-field-dropdown ' . $classses . ' button">Insert field';
		
	echo '<div class="af-dropdown">';
	
	foreach ( $fields as $field ) {
		
		echo sprintf( '<div class="field-option" data-insert-value="{field:%s}">%s</div>', $field['name'], $field['label'] );
		
	}
	
	echo '</div>';
		
	echo '</a>';
	
}



/**
 * Checks if the passed field is a clone field (has the _clone key)
 *
 * @since 1.1.2
 *
 */
function _af_is_clone_field( $field ) {
	
	return isset( $field['_clone'] );
	
}