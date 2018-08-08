<?php


/**
 * The main function used to output a form
 * 
 *
 * @since 1.0.0
 *
 */
function advanced_form( $form_id, $args = array() ) {
	
	// Render form and catch output
	ob_start();
	
	do_action( 'af/form/render', $form_id, $args );
	
	$output = ob_get_clean();
	
	
	if ( ! isset( $args['echo'] ) || $args['echo'] ) {
		echo $output;
	}
	
	
	return $output;
	
}


/**
 * Helepr function to extract a specific field value from submitted fields
 *
 * @since 1.0.0
 *
 */
function af_get_field( $field_key_or_name, $fields = false ) {
	
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		
		$fields = AF()->submission['fields'];
		
	}
	
	
	foreach( $fields as $field ) {
		
		if ( $field['key'] == $field_key_or_name || $field['name'] == $field_key_or_name ) {
			
			return $field['value'];
			
		}
		
		// Also search sub fields
		if ( isset( $field['sub_fields'] ) ) {
			
			foreach ( $field['value'] as $sub_field_name => $sub_field_value ) {
				
				if ( $sub_field_name == $field_key_or_name ) {
					return $sub_field_value;
				}
				
			}
			
		}
		
	}
	
	
	return false;
	
}


/**
 * Save submitted field directly to post
 *
 * @since 1.1.1
 * @deprecated 1.3.0 Use af_save_field()
 * @see af_save_field()
 *
 */
function af_save_field_to_post( $field_key_or_name, $post_id ) {
	
	_deprecated_function( __FUNCTION__, '1.3.0', 'af_save_field()' );
    af_save_field( $field_key_or_name, $post_id );
	
}


/**
 * Save submitted field directly to an object (post, user, term) with ACF naming
 *
 * @since 1.3.0
 *
 */
function af_save_field( $field_key_or_name, $object_id ) {
	
	// Make sure we have a submission to work with
	if ( ! af_has_submission() ) {
		
		return false;
		
	}
	
	
	$field = af_get_field_object( $field_key_or_name );
	
	/**
	 * We save the field directly to the post using acf_update_value.
	 * This ensures that clone fields, repeaters etc. work as intended.
	 * $field['_input'] should match the raw $_POST value.
	 */
	if ( $field ) {
		
		$value = $field['_input'];
		
		acf_update_value( $value, $object_id, $field );
		
		return true;
		
	}
	
	
	return false;
	
}


/**
 * Save all submitted fields directly to an object (post, user, term) with ACF naming
 *
 * @since 1.3.0
 *
 */
function af_save_all_fields( $object_id ) {
	
	// Make sure we have a submission to work with
	if ( ! af_has_submission() ) {
		
		return false;
		
	}
	
	
	$fields = AF()->submission['fields'];
	
	/**
	 * We save the fields directly to the post using acf_update_value.
	 * This ensures that clone fields, repeaters etc. work as intended.
	 * $field['_input'] should match the raw $_POST value.
	 */
	foreach ( $fields as $field ) {
		
		$value = $field['_input'];
		
		acf_update_value( $value, $object_id, $field );
		
	}
	
	
	return true;
	
}


/**
 * Helper function to extract a full field object from submitted fields
 *
 * @since 1.2.0
 *
 */
function af_get_field_object( $field_key_or_name, $fields = false ) {
	
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		
		$fields = AF()->submission['fields'];
		
	}
	
	
	foreach( $fields as $field ) {
		
		// Save submitted value to post using ACFs acf_update_value
		if ( $field['key'] == $field_key_or_name || $field['name'] == $field_key_or_name ) {
			
			return $field;
			
		}
		
	}
	
	
	return false;
	
}


/**
 * Used to register a form programmatically
 *
 * @since 1.0.0
 *
 */
function af_register_form( $form ) {
	
	global $af_registered_forms;
	
	if ( ! $af_registered_forms || ! is_array( $af_registered_forms ) ) {
		
		$af_registered_forms = array();
		
	}
	
	$form = af_get_valid_form( $form );
	
	if ( $form ) {
		
		$af_registered_forms[] = $form;
		
	}
	
	
	return $form;
	
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
 * Validates and fills a form array with default values
 *
 * @since 1.0.0
 *
 */
function af_get_valid_form( $form ) {
	
	// A form key is always required
	if ( ! isset( $form['key'] ) ) {
		return;
	}
	
	$args = array(
		'key'			=> '',
		'post_id' 		=> false,
		'title' 		=> '',
		'display' 		=> array(
			'description' 				=> '',
			'success_message' 			=> '',
		),
		'create_entries' => false,
	);
	
	$args = apply_filters( 'af/form/valid_form', $args );
	
	$form = wp_parse_args( $form, $args );
	
	return $form;
	
}


/**
 * Generates a form array from a form post object
 *
 * @since 1.0.0
 *
 */
function af_form_from_post( $form_post ) {
	
	// Get post object if ID has been passed
	if ( is_numeric( $form_post ) ) {
		$form_post = get_post( $form_post );
	}
	
	// Make sure we have a post and that it's a form
	if ( ! $form_post || 'af_form' != $form_post->post_type ) {
		return false;
	}
	
	
	$form = af_get_valid_form(array(
		'post_id' 		=> $form_post->ID,
		'title' 		=> $form_post->post_title,
		'key'			=> get_post_meta( $form_post->ID, 'form_key', true ),
		'display' 		=> array(
			'description' 				=> get_field( 'form_description', $form_post->ID ),
			'success_message' 			=> get_field( 'form_success_message', $form_post->ID ),
		),
		'create_entries' => get_field( 'form_create_entries', $form_post->ID ),
	));
	
	
	$form = apply_filters( 'af/form/from_post', $form, $form_post );
	$form = apply_filters( 'af/form/from_post/id=' . $form['post_id'], $form, $form_post );
	$form = apply_filters( 'af/form/from_post/key=' . $form['key'], $form, $form_post );
	
	return $form;
	
}


/**
 * Retrieves a form either 
 *
 * @since 1.0.0
 *
 */
function af_form_from_key( $key ) {
	
	global $af_registered_forms;
	
	if ( $af_registered_forms && is_array( $af_registered_forms ) ) {
		
		foreach ( $af_registered_forms as $registered_form ) {
			
			if ( $registered_form['key'] == $key ) {
				
				return af_get_valid_form( $registered_form );
				
			}
			
		}
	
	}
	
	
	// Form not a registered one, search posts by key meta
	$args = array(
		'post_type' => 'af_form',
		'posts_per_page' => '1',
		'meta_query' => array(
			array(
				'key' => 'form_key',
				'value' => $key,
			),
		),
	);
	
	$form_query = new WP_Query( $args );
	
	if ( $form_query->have_posts() ) {
		
		return af_form_from_post( $form_query->posts[0] );
		
	}
	
	
	return false;
	
}


/**
 * Retrieves a form by form key or form ID
 *
 * @since 1.0.0
 *
 */
function af_get_form( $form_id_or_key ) {
	
	$form = false;
	
	
	if ( af_is_valid_form_key( $form_id_or_key ) ) {
		
		$form = af_form_from_key( $form_id_or_key );
		
	} elseif ( is_numeric( $form_id_or_key ) ) {
		
		$form = af_form_from_post( $form_id_or_key );

	}
	
	
	return $form;
	
}

	
/**
 * Returns all forms, both those saved as posts and those registered
 *
 * @since 1.0.0
 *
 */
function af_get_forms() {

	$forms = array();
	
	// Get all forms saved as posts
	$args = array(
		'post_type' => 'af_form',
		'posts_per_page' => -1,
	);
	$form_query = new WP_Query( $args );
	
	if ( $form_query->have_posts() ) {
		
		foreach( $form_query->posts as $form_post ) {
			
			$form = af_form_from_post( $form_post );
			
			$forms[] = $form;
			
		}
		
	}
	
	// Get all programmatically registered forms
	global $af_registered_forms;
	
	if ( $af_registered_forms && is_array( $af_registered_forms ) ) {
		
		foreach( $af_registered_forms as $registered_form ) {
			
			$forms[] = af_get_valid_form( $registered_form );
			
		}
		
	}
	
	return $forms;
	
}


/**
 * Returns all fields groups used by specified form
 *
 * @since 1.0.0
 *
 */
function af_get_form_field_groups( $form_key ) {
	
	// If a full form array is passed
	if ( is_array( $form_key ) ) {
		
		$form_key = $form_key['key'];
		
	}
	
	// Location rule filter
	$args = array(
		'af_form' => $form_key,
	);
	
	$field_groups = acf_get_field_groups( $args );

	return $field_groups;
		
}


/**
 * Returns all fields assigned to a form
 *
 * @since 1.0.1
 *
 */
function af_get_form_fields( $form_key, $type = 'all' ) {
	
	$exclude_types = array();
	
	// Only pick fields which can be properly stringified (not repeaters, flexible fields etc.)
	if ( 'regular' == $type ) {
		$exclude_types = array( 'repeater', 'clone', 'flexible_content' );
	}
	
	
	$form_fields = array();
	
	$field_groups = af_get_form_field_groups( $form_key );
	
	if ( $field_groups ) {
		
		foreach ( $field_groups as $field_group ) {
			
			$fields = acf_get_fields( $field_group );
			
			if ( ! empty ( $fields ) ) {

				foreach ( $fields as $field ) {
					
					if ( in_array( $field['type'], $exclude_types ) ) {
						continue;	
					}
					
					$form_fields[] = $field;
					
				}
				
			}
			
		}
		
	}
	
	return $form_fields;
	
}


/**
 * Adds an error for a specific field.
 * Used as an alternative for acf_add_validation_error with support for field names/keys.
 *
 * @since 1.5.0
 *
 */
function af_add_error( $field_key_or_name, $message ) {
	$field = af_get_field_object( $field_key_or_name );

	if ( $field ) {
		$input_name = sprintf( '%s[%s]', $field['prefix'], $field['key'] );
		acf_add_validation_error( $input_name, $message );
	}
}