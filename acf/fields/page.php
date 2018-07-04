<?php

/**
 * Custom ACF field for creating paged forms. Altered copy of the default tab field.
 *
 * @since 1.5.0
 *
 */
class AF_Page_Field extends acf_field {
  
  function initialize() {
    
    // vars
    $this->name = 'page';
    $this->label = __( 'Page','advanced-forms' );
    $this->category = 'advanced-forms';
    $this->defaults = array();
    
  }
  
  
  /*
  *  render_field()
  *
  *  Create the HTML interface for your field
  *
  *  @param $field - an array holding all the field's data
  *
  *  @type  action
  *  @since 3.6
  *  @date  23/01/13
  */
  
  function render_field( $field ) {
    
    // vars
    $atts = array(
      'href'        => '',
      'class'       => 'af-page-button',
      'data-key'      => $field['key']
    );

    $atts['data-show-numbering'] = ! empty ( $field['show_numbering'] ) ? 'true' : 'false';

    if ( empty( $field['previous_text'] ) ) {
      $field['previous_text'] = __( 'Previous', 'advanced-forms' );
    }

    if ( empty ( $field['next_text'] ) ) {
      $field['next_text'] = __( 'Next', 'advanced-forms' );
    }
    
    ?>
    <a <?php acf_esc_attr_e( $atts ); ?>>
      <span class="title"><?php echo acf_esc_html($field['label']); ?></span>
    </a>

    <button class="af-previous-button button"><?php echo $field['previous_text']; ?></button>
    <button class="af-next-button button"><?php echo $field['next_text']; ?></button>
    <?php
    
    
  }
  
  
  
  /*
  *  render_field_settings()
  *
  *  Create extra options for your field. This is rendered when editing a field.
  *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
  *
  *  @param $field  - an array holding all the field's data
  *
  *  @type  action
  *  @since 3.6
  *  @date  23/01/13
  */
  
  function render_field_settings( $field ) {

    // back_text
    acf_render_field_setting( $field, array(
      'label'     => __( 'Show numbering', 'advanced-forms' ),
      'type'      => 'true_false',
      'name'      => 'show_numbering',
      'ui'        => true,
      'default_value' => true,
    ));

    // back_text
    acf_render_field_setting( $field, array(
      'label'     => __( 'Previous text', 'advanced-forms' ),
      'instructions'  => __( 'Text for the "Previous" button', 'advanced-forms' ),
      'type'      => 'text',
      'name'      => 'previous_text',
      'placeholder' => __( 'Previous', 'advanced-forms' ),
    ));

    // next_text
    acf_render_field_setting( $field, array(
      'label'     => __( 'Next text', 'advanced-forms' ),
      'instructions'  => __( 'Text for the "Next" button', 'advanced-forms' ),
      'type'      => 'text',
      'name'      => 'next_text',
      'placeholder' => __( 'Next', 'advanced-forms' ),
    ));

  }
  
  
  /*
  *  load_field()
  */
  
  function load_field( $field ) {
    
    // remove name to avoid caching issue
    $field['name'] = '';
    
    // remove required to avoid JS issues
    $field['required'] = 0;
    
    // set value other than 'null' to avoid ACF loading / caching issue
    $field['value'] = false;
    
    // return
    return $field;
    
  }
  
}


// initialize
acf_register_field_type( 'AF_Page_Field' );

?>