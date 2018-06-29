<?php
  
  
/**
 * Handles validation, and saving of forms
 * Refactored out of core-forms.php since 1.5.0.
 *
 * @since 1.0.0
 *
 */
class AF_Core_Forms_Submissions {
  
  
  function __construct() {
    
    add_action( 'init', array( $this, 'pre_form' ), 10, 0 );
    
  }
  
  
  /**
   * Handles submissions and enqueue of neccessary scripts
   * Relies on default ACF validations
   *
   * @since 1.0.0
   *
   */
  function pre_form() {

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
      
      
      /**
       * Upload all files in $_FILES using ACFs helper function. Required for basic uploads to work painlessly.
       * TODO: Move to af_save_field() to avoid saving all files?
       *
       * @since 1.3.1
       *
       */
      if ( isset( $_FILES['acf'] ) ) {
        acf_upload_files();
      }
      
      
      do_action( 'af/form/submission', $form, $fields, $args );
      do_action( 'af/form/submission/id=' . $form['post_id'], $form, $fields, $args );
      do_action( 'af/form/submission/key=' . $form['key'], $form, $fields, $args );
      
      
      // Redirect to different URL if redirect argument has been passed
      if ( $args['redirect'] && '' != $args['redirect'] ) {
        
        wp_redirect( $args['redirect'] );
        
        exit;
        
      }
      
      
    }
    
  }
  

  /**
   * Populate AF()->submission with submission data
   * Returns boolean indicating whether a submission was loaded
   *
   * @since 1.5.0
   *
   */
  function load_submission_data() {

    // Make sure a form was posted
    if ( ! ( isset( $_POST['af_form'] ) && ! isset( $_POST['action'] ) ) ) {
      return false;
    }

    // Bail early if already loaded
    if ( AF()->submission ) {
      return true;
    }


    // Load form by key
    $form_key_or_id = $_POST['af_form'];
      
    $form = af_get_form( $form_key_or_id );

    if ( ! $form ) {
      return false;
    }


    // Retrieve the args used to display the form
    $args = json_decode( base64_decode( $_POST['af_form_args'] ), true );

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

    // Save submission data to the global AF object
    AF()->submission = array(
      'form' => $form,
      'args' => $args,
      'fields' => $fields,
    );


    return true;

  }
  
}

return new AF_Core_Forms_Submissions();