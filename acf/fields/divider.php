<?php

if( ! class_exists('AF_Divider_Field') ) :

/**
 * Custom private ACF field used for picking a form field.
 *
 * @since 1.3.2
 *
 */
class AF_Divider_Field extends acf_field {
  
  
  /**
   * Set up field defaults
   *
   * @since 1.3.4
   *
   */
  function __construct() {
    
    // vars
    $this->name = 'divider';
    $this->label = _x('Divider', 'noun', 'acf');
    $this->public = false;
    $this->defaults = array();
    
    // do not delete!
    parent::__construct();
      
  }
  
  
  /**
   * Render interface for field
   *
   * @since 1.3.4
   *
   */
  function render_field( $field ) {
    
  }
  
}


// initialize
acf_register_field_type( new AF_Divider_Field() );

endif; // class_exists check

?>