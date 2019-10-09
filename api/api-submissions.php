<?php


/**
 * Returns true if a successful submission was performed
 *
 * @since 1.1
 */
function af_has_submission( $hash = false ) {
  $submission = AF()->submission;

  if ( is_null( $submission ) ) {
    return false;
  }
  
  if ( $hash && $hash != af_form_instance_hash( $submission['form']['key'], $submission['args'] ) ) {
    return false;
  }
  
  return true;
}


function af_submission_failed( $key = false ) {
  $submission = AF()->submission;

  if ( is_null( $submission ) ) {
    return false;
  }

  if ( $key && $key != $submission['form']['key'] ) {
    return false;
  }

  if ( isset( $submission['errors'] ) && ! empty( $submission['errors'] ) ) {
    return true;
  }

  return false;
}


/**
 * Adds a general error for a form submission.
 * Used during the before_submission hook to stop submission.
 */
function af_add_submission_error( $message ) {
  AF()->submission['errors'][] = $message;
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


/**
 * Calculates a unique hash for a form instance, based on the form key and arguments.
 *
 * @since 1.6.3
 *
 */
function af_form_instance_hash( $form_key, $args ) {
  $args['form'] = $form_key;

  // Sort args to make the hash order-independent
  ksort( $args );

  return md5( json_encode( $args ) );
}