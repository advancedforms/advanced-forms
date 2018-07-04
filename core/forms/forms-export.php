<?php


/**
 * Handles exporting of forms.
 *
 * @since 1.5.0
 *
 */
class AF_Core_Forms_Export {

  static function generate_form_code( $form_id_or_key ) {

    $form = af_get_form( $form_id_or_key );

    if ( ! $form ) {
      return false;
    }

    // Remove post ID key
    unset( $form['post_id'] );

    $str_replace = array(
      "  "      => "\t",
      "'!!__(!!\'"  => "__('",
      "!!\', !!\'"  => "', '",
      "!!\')!!'"    => "')",
      "array ("   => "array("
    );
    $preg_replace = array(
      '/([\t\r\n]+?)array/' => 'array',
      '/[0-9]+ => array/'   => 'array'
    );

    // Create a var_export string of the form array
    $code = var_export( $form, true );
      
    // change double spaces to tabs
    $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
    
    // correctly formats "=> array("
    $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
    
    $code = esc_textarea( $code );

    return sprintf( "\$form = %s;\n\naf_register_form( \$form );", $code );

  }


  /**
   * Generate PHP literal code for a value
   *
   * @since 1.5.0
   *
   */
  static function generate_code( $value, $inset = 1 ) {

    switch(true) {
      case is_null( $value ):
      case is_numeric( $value ):
        return strval( $value );
      case is_string( $value ):
        return sprintf( "'%s'", htmlspecialchars( $value ) );
      case is_array( $value ):
        return self::generate_array_code( $value, $inset );
      case is_bool( $value ) && $value:
        return 'true';
      case is_bool( $value ) && ! $value:
        return 'false';
    }

    return "";

  }


  /**
   * Generate code for an array value. Recursive with the help of generate_code.
   *
   * @since 1.5.0
   *
   */
  static function generate_array_code( $array, $inset = 1 ) {

    $spacing = str_repeat( "\t", $inset );
    $lower_spacing = str_repeat( "\t", $inset - 1 );

    $output = "array(\n";

    foreach ( $array as $key=>$value ) {
      $code = self::generate_code( $value, $inset + 1 );
      $output .= $spacing;

      // Handle both sequential and associative arrays
      if ( is_int( $key ) ) {
        $output .= sprintf( "%s,\n", $code );  
      } else {
        $output .= sprintf( "%s'%s' => %s,\n", $spacing, $key, $code );  
      }
    }

    $output .= $lower_spacing . ')';

    return $output;

  }

}

return new AF_Core_Forms_Export();