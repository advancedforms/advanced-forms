<?php


/**
 * Returns true if a successful submission was performed
 *
 * @since 1.1
 */
function af_has_submission( $key = false ) {
  $submission = AF()->submission;

  if ( is_null( $submission ) ) {
    return false;
  }
  
  if ( $key && $key != $submission['form']['key'] ) {
    return false;
  }
  
  return true;
}


function af_save_session_submission( $submission ) {
	if ( ! af_has_submission() ) {
		return;
	}

  if( '' == session_id() ) {
    session_start();
  }

  $_SESSION['af_submission'] = AF()->submission;
}


function af_get_session_submission() {
	if( '' == session_id() ) {
    session_start();
  }

  if ( isset( $_SESSION['af_submission'] ) ) {
  	return $_SESSION['af_submission'];
  } else {
  	return false;
  }
}


function af_clear_session_submission() {
	if( '' == session_id() ) {
    session_start();
  }

  if ( isset( $_SESSION['af_submission'] ) ) {
  	unset( $_SESSION['af_submission'] );
  }
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