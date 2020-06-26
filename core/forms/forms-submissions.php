<?php
  
  
/**
 * Handles validation, and saving of forms
 * Refactored out of core-forms.php since 1.5.0.
 *
 * @since 1.0.0
 *
 */
class AF_Core_Forms_Submissions {
  const DEFAULT_COOKIE_NAME = 'af_submission';

  const OPTION_EXPIRY_MINUTES = 5;
  const OPTION_DATA_PREFIX = 'af_submission_data_';
  const OPTION_EXPIRY_PREFIX = 'af_submission_expiry_';
  
  function __construct() {
    add_action( 'init', array( $this, 'pre_form' ), 10, 0 );
    add_action( 'acf/validate_save_post', array( $this, 'validate' ), 10, 0 );
    add_filter( 'acf/upload_prefilter', array( $this, 'intercept_upload_errors' ), 1000, 3 );
  }
  
  
  /**
   * Handles submissions and enqueue of neccessary scripts
   * Relies on default ACF validations
   *
   * @since 1.0.0
   *
   */
  function pre_form() {
    // Make sure this is not an AJAX validation request
    if ( isset ( $_POST['action'] ) ) {
      return;
    }

    // Make sure honeypot field is empty if one exists
    if ( isset( $_POST['email_for_non_humans'] ) && ! empty( $_POST['email_for_non_humans'] ) ) {
      wp_die( 'Non-human user detected' );
      exit;
    }

    // Try loading submission data
    if ( ! $this->load_submission_data() ) {
      return;
    }
      
    // Validate the posted data, this validation has already been performed once over AJAX
    if ( acf_validate_save_post( true ) ) {
      $form = AF()->submission['form'];
      $args = AF()->submission['args'];
      $fields = AF()->submission['fields'];
      
      // Increase the form submissions counter
      if ( $form['post_id'] ) {
        $submissions = get_post_meta( $form['post_id'], 'form_num_of_submissions', true );
        $submissions = $submissions ? $submissions + 1 : 1;
        update_post_meta( $form['post_id'], 'form_num_of_submissions', $submissions );
      }

      do_action( 'af/form/before_submission', $form, $fields, $args );
      do_action( 'af/form/before_submission/id=' . $form['post_id'], $form, $fields, $args );
      do_action( 'af/form/before_submission/key=' . $form['key'], $form, $fields, $args );
      
      if ( ! af_submission_failed() ) {
        do_action( 'af/form/submission', $form, $fields, $args );
        do_action( 'af/form/submission/id=' . $form['post_id'], $form, $fields, $args );
        do_action( 'af/form/submission/key=' . $form['key'], $form, $fields, $args );
      }

      // Redirect to different URL if redirect argument has been passed
      if ( $args['redirect'] && '' != $args['redirect'] ) {
        $this->clear_expired_submissions();
        $this->save_submission( AF()->submission );

        wp_redirect( $args['redirect'] );
        exit;
      }
    }
  }


  /**
   * Handles validation of a form.
   * Adds custom validation actions specific to forms.
   *
   * @since 1.5.0
   *
   */
  function validate() {
    // Try loading submission data
    if ( ! $this->load_submission_data() ) {
      return;
    }

    $form = AF()->submission['form'];
    $args = AF()->submission['args'];

    do_action( 'af/form/validate', $form, $args );
    do_action( 'af/form/validate/id=' . $form['post_id'], $form, $args );
    do_action( 'af/form/validate/key=' . $form['key'], $form, $args );
  }
  

  /**
   * Populate AF()->submission with submission data
   * Returns boolean indicating whether a submission was loaded
   *
   * @since 1.5.0
   *
   */
  function load_submission_data() {
    // Check if there is a cookie-passed submission
    if ( $submission = $this->get_submission() ) {
      AF()->submission = $submission;

      // Return false to stop the submission from being processed again
      return false;
    }

    // Make sure a form was posted
    if ( ! ( isset( $_POST['af_form'] ) ) ) {
      return false;
    }

    // Bail early if already loaded
    if ( AF()->submission ) {
      return true;
    }

    /**
     * Upload all files in $_FILES using ACFs helper function. Required for basic uploads to work painlessly.
     * TODO: Move to af_save_field() to avoid saving all files?
     *
     * @since 1.3.1
     *
     */
    if ( isset( $_FILES['acf'] ) ) {
      $this->clear_upload_errors();
      acf_upload_files();
      $this->handle_upload_errors();
    }

    // Generate submission from data
    $submission = $this->create_submission();
    if ( ! $submission ) {
      return false;
    }

    // Save submission data to the global AF object
    AF()->submission = $submission;

    return true;
  }


  /**
   * Create a submission object from the request data.
   * Returns a submission array or false on failure.
   *
   * @since 1.6.0
   *
   */
  function create_submission() {
    // Load form by key
    $form_key_or_id = $_POST['af_form'];
      
    $form = af_get_form( $form_key_or_id );

    if ( ! $form ) {
      return false;
    }

    // Retrieve the args used to display the form
    $encoded_args = $_POST['af_form_args'];
    $args = json_decode( base64_decode( $encoded_args ), true );

    // Verify nonce
    $nonce = $_POST['af_form_nonce'];
    $hashed_args = hash( 'sha256', $encoded_args );
    $nonce_value = sprintf( 'af_submission_%s_%s', $form['key'], $hashed_args );
    if ( ! wp_verify_nonce( $nonce, $nonce_value ) ) {
      wp_die( 'Invalid form nonce' );
      exit;
    }

    // Retrieve all form fields and their values
    $fields = array();
    
    if ( isset( $_POST['acf'] ) ) {
      foreach ( $_POST['acf'] as $k => $value ) {
        $field = acf_get_field( $k );
        
        $field['_input'] = $value;
        $field['value'] = acf_format_value( $value, 0, $field );
        
        $fields[] = $field;
      }
    }

    return array(
      'form' => $form,
      'args' => $args,
      'fields' => $fields,
      'errors' => array(),
    );
  }

  /**
   * Fetch a submission from options if the submission cookie is set.
   * Will return false if the cookie is not set or the submission does not exist in the database.
   * 
   * @since 1.6.6
   *
   */
  private function get_submission() {
    if ( ! isset( $_COOKIE[ $this->get_cookie_name() ] ) ) {
      return false;
    }

    $key = $_COOKIE[ $this->get_cookie_name() ];
    $submission = get_option( self::OPTION_DATA_PREFIX . $key, false );

    $this->delete_submission( $key );
    setcookie( $this->get_cookie_name(), '', time() - HOUR_IN_SECONDS, '/' );

    return $submission;
  }
  
  /**
   * Save a submission to options and set a cookie with a reference to it.
   * Submissions are identified by a randomly generated key stored in a cookie.
   *
   * @since 1.6.6
   *
   */
  private function save_submission( $submission ) {
    $key = wp_generate_password( 12, false, false );

    $expiration_time = time() + self::OPTION_EXPIRY_MINUTES * MINUTE_IN_SECONDS;

    add_option( self::OPTION_DATA_PREFIX . $key, $submission );
    add_option( self::OPTION_EXPIRY_PREFIX . $key, $expiration_time );

    setcookie( $this->get_cookie_name(), $key, $expiration_time, '/' );
  }

  /**
   * Delete a submission from options based on key
   *
   * @since 1.6.6
   *
   */
  private function delete_submission( $key ) {
    delete_option( self::OPTION_DATA_PREFIX . $key );
    delete_option( self::OPTION_EXPIRY_PREFIX . $key );
  }

  private function get_cookie_name() {
    return apply_filters( 'af/settings/cookie_name', self::DEFAULT_COOKIE_NAME );
  }

  /**
   * Remove any expired submission from options which have not been cleared automatically.
   * If a request fails a created submission could potentially not be removed from the database.
   *
   * @since 1.6.6
   *
   */
  private function clear_expired_submissions() {
    global $wpdb;

    $options_table = $wpdb->prefix . 'options';
    $name_pattern = self::OPTION_EXPIRY_PREFIX . '%';
    $current_time = time();

    // Find all expired submissions in the options table.
    // This query is very efficient because of the index on the name column.
    $expired_submissions = $wpdb->get_col("
      SELECT option_name
      FROM $options_table
      WHERE option_name LIKE '$name_pattern'
        AND option_value < $current_time
    ");

    foreach ( $expired_submissions as $option_name ) {
      // Find submission key by removing prefix from option name.
      $submission_key = substr( $option_name, strlen( self::OPTION_EXPIRY_PREFIX ) );
      $this->delete_submission( $submission_key );
    }
  }

  /**
   * ACF doesn't provide a simple way of catching upload errors when using the basic uploader.
   * This function is hooked into the "acf/upload_prefilter" with a high priority.
   * It will intercept all upload errors and save them together with field data.
   *
   * @since 1.7.0
   *
   */
  function intercept_upload_errors( $errors, $file, $field ) {
    if ( ! empty( $errors ) ) {
      $this->upload_errors[ $field['key'] ] = array(
        'field' => $field,
        'messages' => $errors,
      );
    }

    return $errors;
  }

  /**
   * Removes all intercepted upload errors.
   * Should be run before handling uploads using "acf_upload_files()".
   *
   * @since 1.7.0
   *
   */
  private function clear_upload_errors() {
    $this->upload_errors = array();
  }

  /**
   * Checks if any upload errors have been caught and stops the submission.
   * This is a very rudimentary way of handling upload errors but it's necessary as ACF can't handle errors when using the basic uploader.
   * The errors checks should in the future be implemented client-side for a good user experience and this is mostly meant to be a fallback.
   * 
   *
   * @since 1.7.0
   *
   */
  private function handle_upload_errors() {
    if ( empty( $this->upload_errors ) ) {
      return;
    }

    $message = sprintf( '<h2>%s</h2>', __('Validation failed', 'acf') );
    $message .= '<ul>';
    foreach( $this->upload_errors as $error ) {
      $field = $error['field'];
      foreach ( $error['messages'] as $error_message ) {
        $message .= '<li>' . sprintf( '%s: %s', $field['label'], $error_message ) . '</li>';
      }
    }
    $message .= '</ul>';

    wp_die( $message, __('Validation failed', 'acf') );
  }
}

return new AF_Core_Forms_Submissions();