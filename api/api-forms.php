<?php


/**
 * The main function used to output a form
 * 
 *
 * @since 1.0.0
 *
 */
function advanced_form( $form_id, $args = array() ) {
	
	do_action( 'af/form/render', $form_id, $args );
	
}


/**
 * Helepr function to extract a specific field value from submitted fields
 *
 * @since 1.0.0
 *
 */
function af_get_field( $field_key_or_name, $fields ) {
	
	foreach( $fields as $field ) {
		
		if ( $field['key'] == $field_key_or_name || $field['name'] == $field_key_or_name ) {
			
			return $field['value'];
			
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
	
	$form = wp_parse_args( $form, array(
		'post_id' 		=> false,
		'title' 		=> '',
		'key'			=> '',
		'display' 		=> array(
			'display_title' 			=> false,
			'display_description' 		=> false,
			'description' 				=> '',
			'success_message' 			=> '',
		),
		'create_entries' => false,
	));
	
	
	$form = apply_filters( 'af/form/valid_form', $form );
	
	
	return $form;
	
}


/**
 * Generates a form array from a form post object
 *
 * @since 1.0.0
 *
 */
function af_form_from_post( $post ) {
	
	// Get post object if ID has been passed
	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}
	
	
	// Make sure we have a post and that it's a form
	if ( !$post || 'af_form' != $post->post_type ) {
		return false;
	}
	
	
	$form = array(
		'post_id' 		=> $post->ID,
		'title' 		=> $post->post_title,
		'key'			=> get_post_meta( $post->ID, 'form_key', true ),
		'display' 		=> array(
			'display_title' 			=> get_field( 'form_display_title', $post->ID ),
			'display_description' 		=> get_field( 'form_display_description', $post->ID ),
			'description' 				=> get_field( 'form_description', $post->ID ),
			'success_message' 			=> get_field( 'form_success_message', $post->ID ),
		),
		'create_entries' => get_field( 'form_create_entries', $post->ID ),
	);
	
	
	$form = apply_filters( 'af/form/from_post', $form, $post );
	$form = apply_filters( 'af/form/from_post/id=' . $form['post_id'], $form, $post );
	$form = apply_filters( 'af/form/from_post/key=' . $form['key'], $form, $post );
	
	
	return af_get_valid_form( $form );
	
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
	
	$query = new WP_Query( $args );
	
	if ( $query->have_posts() ) {
		
		return af_form_from_post( $query->posts[0] );
		
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
	
	if ( is_numeric( $form_id_or_key ) ) {
		
		$form = af_form_from_post( $form_id_or_key );
		
	}
	
	
	// Form should be loaded as key
	if (!$form) {
		
		$form = af_form_from_key( $form_id_or_key );
		
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
		
		while ( $form_query->have_posts() ) {
			$form_query->the_post();
			
			global $post;
			
			$form = af_form_from_post( $post );
			
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