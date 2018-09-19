<?php
  

/**
 * Handles rendering of forms.
 * Refactored out of core-forms.php since 1.5.0.
 *
 * @since 1.5.0
 *
 */
class AF_Core_Forms_Rendering {
  
  
  function __construct() {

    add_shortcode( 'advanced_form', array( $this, 'form_shortcode' ) );
    
    add_action( 'af/form/render', array( $this, 'render' ), 10, 2 );
    
  }


  /**
   * Registers the shortcode advanced_form which renders the form specified by the "form" attribute
   *
   * @since 1.0.0
   *
   */
  function form_shortcode( $atts ) {
    
    if ( isset( $atts['form'] ) ) {
      
      $form_id_or_key = $atts['form'];
      unset( $atts['form'] );
      
      ob_start();
      
      $this->render( $form_id_or_key, $atts );
      
      $output = ob_get_clean();
      
      return $output;
      
    }
    
  }
  
  
  /**
   * Renders the form specified by ID
   *
   * @since 1.0.0
   *
   */
  function render( $form_id_or_key, $args ) {
    
    $form = af_get_form( $form_id_or_key );
    
    if ( ! $form ) {
      return;
    }
    
    
    /**
     * Enqueue ACF scripts and styles
     *
     * Normally ACF initializes the global JS object in wp_head but we only want to include the scripts when displaying a form.
     * To work around this we enqueue using the regular ACF function and then immediately include the acf-input.js script and all it's dependencies.
     * If acf-input.js is not initialized before the fields then conditional logic doesn't work. The remaining scripts/styles will be included in wp_footer.
     *
     * From ACF 5.7 and onwards this is no longer necessary. Conditional logic is no longer reliant on inline scripts and a regular enqueue is sufficient.
     *
     * @since 1.1.1
     *
     */
    acf_enqueue_scripts();
    wp_enqueue_script( 'af-forms-script', AF()->url . 'assets/js/forms.js', array( 'jquery', 'acf-input' ), AF()->version, true );
    
    // Check if ACF version is < 5.7
    if ( acf_version_compare( acf()->version, '<', '5.7' ) ) {
      global $wp_scripts;
      
      $wp_scripts->print_scripts( array( 'acf-input', 'acf-pro-input' ) );
    }
    
    
    // Allow the form to be modified before rendering form
    $form = apply_filters( 'af/form/before_render', $form, $args );
    $form = apply_filters( 'af/form/before_render/id=' . $form['post_id'], $form, $args );
    $form = apply_filters( 'af/form/before_render/key=' . $form['key'], $form, $args );
    
    
    $args = wp_parse_args($args, array(
      'display_title'       => false,
      'display_description'     => false,
      'id'            => $form['key'],
      'values'          => array(),
      'submit_text'         => __( 'Submit', 'advanced-forms' ),
      'redirect'          => acf_get_current_url(),
      'target'          => acf_get_current_url(),
      'echo'            => true,
      'exclude_fields'      => array(),
      'uploader'          => 'wp',
      'filter_mode'       => false,
      'label_placement' => 'top',
    ));
    
    
    // Allow the arguments to be modified before rendering form
    $args = apply_filters( 'af/form/args', $args, $form );
    $args = apply_filters( 'af/form/args/id=' . $form['post_id'], $args, $form );
    $args = apply_filters( 'af/form/args/key=' . $form['key'], $args, $form );
    
    
    // Form element
    $form_attributes = array(
      'class'   => 'af-form acf-form',
      'method'  => 'POST',
      'action'  => $args['target'],
      'id'    => $args['id'],
    );
    
    $form_attributes = apply_filters( 'af/form/attributes', $form_attributes, $form, $args );
    $form_attributes = apply_filters( 'af/form/attributes/id=' . $form['post_id'], $form_attributes, $form, $args );
    $form_attributes = apply_filters( 'af/form/attributes/key=' . $form['key'], $form_attributes, $form, $args );
    
    echo sprintf( '<form %s>', acf_esc_atts( $form_attributes ) );
    
    
    do_action( 'af/form/before_title', $form, $args );
    do_action( 'af/form/before_title/id=' . $form['post_id'], $form, $args );
    do_action( 'af/form/before_title/key=' . $form['key'], $form, $args );
    
    
    // Display title
    if ( $args['display_title'] ) {
      
      echo sprintf( '<h1 class="af-title">%s</h1>', $form['title'] );
      
    }
    
    
    // Display description
    if ( $args['display_description'] ) {
      
      echo sprintf( '<div class="af-description">%s</div>', $form['display']['description'] );
      
    }
    
    
    /**
     * Check if form should be restricted and not displayed.
     * Filter will return false if no restriction is applied otherwise it will return a string to display.
     */
    $restriction = false;
    $restriction = apply_filters( 'af/form/restriction', $restriction, $form, $args );
    $restriction = apply_filters( 'af/form/restriction/id=' . $form['post_id'], $restriction, $form, $args );
    $restriction = apply_filters( 'af/form/restriction/key=' . $form['key'], $restriction, $form, $args );
    
    
    // Display success message, restriction message, or fields
    if ( af_has_submission() && ! $args['filter_mode'] ) {

      $success_message = $form['display']['success_message'];
      $success_message = apply_filters( 'af/form/success_message', $success_message, $form, $args );
      $success_message = apply_filters( 'af/form/success_message/id=' . $form['post_id'], $success_message, $form, $args );
      $success_message = apply_filters( 'af/form/success_message/key=' . $form['key'], $success_message, $form, $args );

      $success_message = af_resolve_field_includes( $success_message );
      
      echo '<div class="af-success">';
      
        echo $success_message;
      
      echo '</div>';
      
    } elseif ( $restriction ) {
    
      echo '<div class="af-restricted-message">';
      
        echo $restriction;
      
      echo '</div>';
    
    } else {

      // Increase the form view counter
      if ( $form['post_id'] ) {
        $views = get_post_meta( $form['post_id'], 'form_num_of_views', true );
        $views = $views ? $views + 1 : 1;
        update_post_meta( $form['post_id'], 'form_num_of_views', $views );
      }
      
      // Set ACF uploader type setting
      acf_update_setting( 'uploader', $args['uploader'] );
      
      
      // Get field groups for the form and display their fields
      $field_groups = af_get_form_field_groups( $form['key'] );
      
      
      echo sprintf( '<div class="af-fields acf-fields acf-form-fields -%s">', $args['label_placement'] );
      
      
      do_action( 'af/form/before_fields', $form, $args );
      do_action( 'af/form/before_fields/id=' . $form['post_id'], $form, $args );
      do_action( 'af/form/before_fields/key=' . $form['key'], $form, $args );
      

      // Form data required by ACF for validation to work.
      acf_form_data(array( 
        'screen'  => 'acf_form',
        'post_id' => false,
        'form'    => false,
      ));

      // Hidden fields to identify form
      echo '<div class="acf-hidden">';

        $nonce = wp_create_nonce( 'acf_nonce' );
        echo sprintf( '<input type="hidden" name="_acfnonce" value="%s">', $nonce );
        echo sprintf( '<input type="hidden" name="nonce" value="%s">', $nonce );
      
        echo sprintf( '<input type="hidden" name="af_form" value="%s">', $form['key'] );
        echo sprintf( '<input type="hidden" name="af_form_args" value="%s">', base64_encode( json_encode( $args ) ) );
        echo sprintf( '<input type="hidden" name="_acf_form" value="%s">', base64_encode( json_encode( $args ) ) );
        
        do_action( 'af/form/hidden_fields', $form, $args );
        do_action( 'af/form/hidden_fields/id=' . $form['post_id'], $form, $args );
        do_action( 'af/form/hidden_fields/key=' . $form['key'], $form, $args );
        
      echo '</div>';
      
      
      foreach ( $field_groups as $field_group ) {
        
        // Get all fields for field group
        $fields = acf_get_fields( $field_group );
        
        foreach ( $fields as $field ) {
          
          // Skip field if it is in the exluded fields argument
          if ( isset( $args['exclude_fields'] ) && is_array( $args['exclude_fields'] ) ) {
            
            if ( in_array( $field['key'], $args['exclude_fields'] ) || in_array( $field['name'], $args['exclude_fields'] ) ) {
              continue;
            }
            
          }
          
          $this->render_field( $field, $form, $args );
          
        }
        
      }
      
      
      // Submit button and loading indicator
      $button_attributes = array();

      $button_attributes['class'] = 'acf-button af-submit-button';

      $button_attributes = apply_filters( 'af/form/button_attributes', $button_attributes, $form, $args );
      $button_attributes = apply_filters( 'af/form/button_attributes/id=' . $form['post_id'], $button_attributes, $form, $args );
      $button_attributes = apply_filters( 'af/form/button_attributes/key=' . $form['key'], $button_attributes, $form, $args );

      echo '<div class="af-submit acf-form-submit">';
        echo sprintf( '<button type="submit" %s>%s</button>', acf_esc_atts( $button_attributes ), $args['submit_text'] );
        echo '<span class="acf-spinner af-spinner"></span>';
      echo '</div>';
      
      
      do_action( 'af/form/after_fields', $form, $args );
      do_action( 'af/form/after_fields/id=' . $form['post_id'], $form, $args );
      do_action( 'af/form/after_fields/key=' . $form['key'], $form, $args );
      
      // End fields wrapper
      echo '</div>';
    
    }
    
    // End form
    echo '</form>';
    
  }


  /**
   * Renders a single field as part of a form.
   * 
   * @since 1.5.0
   *
   */
  function render_field( $field, $form, $args ) {

    // Include default value
    if ( empty( $field['value'] ) && isset( $field['default_value'] ) ) {
      $field['value'] = $field['default_value'];
    }
    
    
    // Include pre-fill values (either through args or filter)
    if ( isset( $args['values'][ $field['name'] ] ) ) {
      $field['value'] = $args['values'][ $field['name'] ];
    }
    
    if ( isset( $args['values'][ $field['key'] ] ) ) {
      $field['value'] = $args['values'][ $field['key'] ];
    }
    
    $field['value'] = apply_filters( 'af/field/prefill_value', $field['value'], $field, $form, $args );
    $field['value'] = apply_filters( 'af/field/prefill_value/name=' . $field['name'], $field['value'], $field, $form, $args );
    $field['value'] = apply_filters( 'af/field/prefill_value/key=' . $field['key'], $field['value'], $field, $form, $args );
    
    
    // Include any previously submitted value
    if ( isset( $_POST['acf'][ $field['key'] ] ) ) {
    
      $field['value'] = $_POST['acf'][ $field['key'] ];
    
    }
    
    
    // Attributes to be used on the wrapper element
    $attributes = array();
    
    $attributes['id'] = $field['wrapper']['id'];
    
    $attributes['class'] = $field['wrapper']['class'];
    
    $attributes['class'] .= sprintf( ' af-field af-field-type-%s af-field-%s acf-field acf-field-%s acf-field-%s', $field['type'], $field['name'], $field['type'], $field['key'] );
    
    if ( $field['required'] ) {
      $attributes['class'] .= ' af-field-required';
    }

    
    // This is something ACF needs
    $attributes['class'] = str_replace( '_', '-', $attributes['class'] );
    $attributes['class'] = str_replace( 'field-field-', 'field-', $attributes['class'] );
    
    
    $width = $field['wrapper']['width'];
    
    if ( $width ) {
      
      $attributes['data-width'] = $width;
      $attributes['style'] = 'width: ' . $width . '%;';
      
    }
    
    $attributes['data-name'] = $field['name'];
    $attributes['data-key'] = $field['key'];
    $attributes['data-type'] = $field['type'];

    /**
     * ACF 5.7 totally changes how conditional logic works.
     * Instead of running a script after each field we now pass the conditional rules JSON encoded to the data-conditions attribute.
     *
     * @since 1.4.0
     *
     */
    if( ! empty( $field['conditional_logic'] ) ) {
      $field['conditions'] = $field['conditional_logic'];
    }
    
    if( ! empty( $field['conditions'] ) ) {
      $attributes['data-conditions'] = $field['conditions'];
    }
    
    
    $attributes = apply_filters( 'af/form/field_attributes', $attributes, $field, $form, $args );
    $attributes = apply_filters( 'af/form/field_attributes/id=' . $form['post_id'], $attributes, $field, $form, $args );
    $attributes = apply_filters( 'af/form/field_attributes/key=' . $form['key'], $attributes, $field, $form, $args );
    
    
    // Field wrapper
    echo sprintf( '<div %s>', acf_esc_atts( $attributes ) );
    
    
    echo '<div class="af-label acf-label">';
    
      $label = $field['label'];
      
      $label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';
      
      echo sprintf( '<label for="acf-%s">%s</label>', $field['key'], $label );
      
    echo '</div>';
    
    
    if ( '' != $field['instructions'] ) {
      echo sprintf( '<p class="af-field-instructions">%s</p>', $field['instructions'] );
    }
    
    
    echo '<div class="af-input acf-input">';
    
      // Render field with default ACF
      acf_render_field( $field );
    
    echo '</div>';
    
    
    /*
     * Conditional logic Javascript for field.
     * This is not needed after ACF 5.7 and won't be included.
     */
    if ( acf_version_compare( acf()->version, '<', '5.7' ) ) {
      if ( ! empty( $field['conditional_logic'] ) ) {
        ?>
        <script type="text/javascript">
          if(typeof acf !== 'undefined'){ acf.conditional_logic.add( '<?php echo $field['key']; ?>', <?php echo json_encode($field['conditional_logic']); ?>); }
        </script>
        <?php
      }
    }
    
    
    // End field wrapper
    echo '</div>';

  }
  
  
}

return new AF_Core_Forms_Rendering();