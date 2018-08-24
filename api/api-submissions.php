<?php


/**
 * Returns true if a successful submission was performed
 *
 * @since 1.1
 */
function af_has_submission() {
  
  return ! is_null( AF()->submission );
  
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