<?php

/**
 * The main function used to output a form
 *
 * @since 1.0.0
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
 * Helper function to extract a specific field value from submitted fields
 *
 * @since 1.0.0
 */
function af_get_field( $field_key_or_name, $fields = false ) {
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		$fields = AF()->submission['fields'];
	}

	foreach ( $fields as $field ) {
		if ( $field['key'] == $field_key_or_name || $field['name'] == $field_key_or_name ) {
			return $field['value'];
		}

		// Also search sub fields
		if ( isset( $field['sub_fields'] ) && is_array( $field['value'] ) ) {
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
 */
function af_save_field_to_post( $field_key_or_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.3.0', 'af_save_field()' );
	af_save_field( $field_key_or_name, $post_id );
}

/**
 * Save submitted field directly to an object (post, user, term) with ACF naming
 *
 * @since 1.3.0
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
 */
function af_save_all_fields( $object_id, $excluded_fields = array() ) {
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
		if ( in_array( $field['key'], $excluded_fields ) ) {
			continue;
		}

		$value = $field['_input'];
		acf_update_value( $value, $object_id, $field );
	}

	return true;
}

/**
 * Helper function to extract a full field object from submitted fields
 *
 * @since 1.2.0
 */
function af_get_field_object( $field_key_or_name, $fields = false ) {
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		$fields = AF()->submission['fields'];
	}

	foreach ( $fields as $field ) {
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
 */
function af_register_form( $form ) {
	global $af_registered_forms;

	if ( ! $af_registered_forms || ! is_array( $af_registered_forms ) ) {
		$af_registered_forms = array();
	}

	$form = af_get_valid_form( $form );

	if ( $form ) {
		$af_registered_forms[ $form['key'] ] = $form;
	}

	return $form;
}

/**
 * Checks if the passed key is a valid form key (begins with form_)
 *
 * @since 1.0.1
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
 */
function af_get_valid_form( $form ) {
	// A form key is always required
	if ( ! isset( $form['key'] ) ) {
		return false;
	}

	$args = array(
		'key' => '',
		'post_id' => false,
		'title' => '',
		'display' => array(
			'description' => '',
			'success_message' => '',
		),
		'create_entries' => false,
	);

	$args = apply_filters( 'af/form/valid_form', $args );

	return wp_parse_args( $form, $args );
}

/**
 * Generates a form array from a form post object
 *
 * @since 1.0.0
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

	$form = af_get_valid_form( array(
		'post_id' => $form_post->ID,
		'title' => $form_post->post_title,
		'key' => get_post_meta( $form_post->ID, 'form_key', true ),
		'display' => array(
			'description' => get_field( 'form_description', $form_post->ID ),
			'success_message' => get_field( 'form_success_message', $form_post->ID ),
		),
		'create_entries' => get_field( 'form_create_entries', $form_post->ID ),
	) );

	$form = apply_filters( 'af/form/from_post', $form, $form_post );
	$form = apply_filters( 'af/form/from_post/id=' . $form['post_id'], $form, $form_post );
	$form = apply_filters( 'af/form/from_post/key=' . $form['key'], $form, $form_post );

	return $form;
}

/**
 * Save a form array to a form post.
 *
 * @since 1.7.0
 */
function af_form_to_post( $form, $post ) {
	// Get post object if ID has been passed
	if ( is_numeric( $post ) ) {
		$post = get_post( $post );
	}

	wp_update_post( array(
		'ID' => $post->ID,
		'post_title' => $form['title'],
	) );

	$form = af_get_valid_form( $form );

	update_post_meta( $post->ID, 'form_key', $form['key'] );

	update_field( 'field_form_description', $form['display']['description'], $post->ID );
	update_field( 'field_form_success_message', $form['display']['success_message'], $post->ID );

	update_field( 'field_form_create_entries', $form['create_entries'], $post->ID );

	$form = do_action( 'af/form/to_post', $form, $post );
	$form = do_action( 'af/form/to_post/id=' . $post->ID, $form, $post );
	$form = do_action( 'af/form/to_post/key=' . $form['key'], $form, $post );

	return $post;
}

/**
 * Retrieves a form either
 *
 * @since 1.0.0
 */
function af_form_from_key( $key ) {
	global $af_registered_forms;

	if ( $af_registered_forms && isset( $af_registered_forms[ $key ] ) ) {
		return af_get_valid_form( $af_registered_forms[ $key ] );
	}

	// Form not a registered one, search posts by key meta
	$post = af_form_post_from_key( $key );
	if ( $post ) {
		return af_form_from_post( $post );
	}

	return false;
}

/**
 * Retrieves a form post by key if one exists.
 *
 * @since 1.7.0
 */
function af_form_post_from_key( $key ) {
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
		return $form_query->posts[0];
	}

	return false;
}

/**
 * Retrieves a form by form key or form ID
 *
 * @since 1.0.0
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
 */
function af_get_forms() {
	$forms = array();

	// Get all forms saved as posts
	$form_query = new WP_Query( [
		'post_type' => 'af_form',
		'posts_per_page' => - 1,
	] );

	if ( $form_query->have_posts() ) {
		foreach ( $form_query->posts as $form_post ) {
			$form = af_form_from_post( $form_post );
			$forms[] = $form;
		}
	}

	// Get all programmatically registered forms
	global $af_registered_forms;

	if ( $af_registered_forms && is_array( $af_registered_forms ) ) {
		foreach ( $af_registered_forms as $registered_form ) {
			$forms[] = af_get_valid_form( $registered_form );
		}
	}

	return $forms;
}

/**
 * Returns all fields groups used by specified form
 *
 * @since 1.0.0
 */
function af_get_form_field_groups( $form_key ) {
	// If a full form array is passed
	if ( ! empty( $form_key['key'] ) ) {
		$form_key = $form_key['key'];
	}

	$field_groups = acf_get_field_groups( [ 'af_form' => $form_key, ] );

	$field_groups = apply_filters( 'af/form/field_groups', $field_groups, $form_key );
	$field_groups = apply_filters( "af/form/field_groups/key={$form_key}", $field_groups, $form_key );

	return $field_groups;
}

/**
 * Returns all fields assigned to a form
 *
 * @since 1.0.1
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
 * Renders the success message for a form. Requires that the submission has already been loaded.
 *
 * @since 1.7.2
 */
function af_form_success_message( $form, $args ) {
	$success_message = $form['display']['success_message'];
	$success_message = apply_filters( 'af/form/success_message', $success_message, $form, $args );
	$success_message = apply_filters( 'af/form/success_message/id=' . $form['post_id'], $success_message, $form, $args );
	$success_message = apply_filters( 'af/form/success_message/key=' . $form['key'], $success_message, $form, $args );

	$success_message = af_resolve_merge_tags( $success_message );

	return sprintf( '<div class="af-success" aria-live="assertive" role="alert">%s</div>', $success_message );
}

/**
 * Enqueues the necessary scripts and styles for a form.
 *
 * @since 1.8.0
 */
function af_enqueue() {
	// Enqueue the hotfix that prevents validation from firing across all forms on the same page when one is submitted.
	if ( apply_filters( 'af/settings/enqueue_validation_hotfix', true ) ) {
		wp_enqueue_script( 'af-multi-form-validation-hotfix', AF()->url . 'assets/dist/js/multi-form-validation-hotfix.js', [ 'acf-input' ] );
	}

	// Enqueue ACF scripts and styles
	acf_enqueue_scripts();

	// ACF fails to include all translations when running "acf_enqueue_scripts", hence we need to do it manually.
	$acf_l10n = acf_get_instance( 'ACF_Assets' )->text;
	wp_localize_script( 'acf-input', 'acfL10n', $acf_l10n );

	wp_enqueue_script( 'af-forms-script', AF()->url . 'assets/dist/js/forms.js', array(
		'jquery',
		'acf-input'
	), AF()->version, true );

	wp_enqueue_style( 'af-form-style', AF()->url . 'assets/dist/css/form.css' );
}