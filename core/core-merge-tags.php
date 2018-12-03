<?php

class AF_Core_Merge_Tags {

  function __construct() {
    add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_all_fields_tag' ), 10, 3 );
    add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_sub_field_tag' ), 10, 3 );
    add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_field_tag' ), 10, 3 );
  }


  /**
   * Resolve merge tags on the form {all_fields} to render all field values as a table.
   *
   * @since 1.6.0
   *
   */
  function resolve_all_fields_tag( $output, $tag, $fields ) {
    if ( ! empty( $output ) || 'all_fields' != $tag ) {
      return $output;
    }

    $output = '<table class="af-field-include">';
    
    foreach ( $fields as $field ) {
      
      if ( 'clone' == $field['type'] ) {
        
        foreach ( $field['sub_fields'] as $sub_field ) {
          
          $output .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
          $output .= sprintf( '<tr><td>%s</td></tr>', _af_render_field_include( $sub_field, $field['value'][ $sub_field['name'] ] ) );
          
        }
        
      } else {
      
        $output .= sprintf( '<tr><th>%s</th></tr>', $field['label'] );
        $output .= sprintf( '<tr><td>%s</td></tr>', _af_render_field_include( $field ) );
      
      }
      
    }
    
    $output .= '</table>';

    return $output;
  }


  /**
   * Resolve merge tags on the form {field:FIELD_NAME[SUBFIELD_NMAE]} to render a single subfield.
   * Needs to be apply before resolve_field_tag to avoid override.
   *
   * @since 1.6.0
   *
   */
  function resolve_sub_field_tag( $output, $tag, $fields ) {
    if ( ! empty( $output ) || ! preg_match_all( '/field:(.*)\[(.*?)\]/', $tag, $matches ) ) {
      return $output;
    }

    $field_name = $matches[1][0];
    $sub_field_name = $matches[2][0];
    $field = af_get_field_object( $field_name, $fields );

    if ( isset( $field['sub_fields'] ) ) {
      $sub_field = af_get_field_object( $sub_field_name, $field['sub_fields'] );
      $value = _af_render_field_include( $sub_field, $field['value'][ $sub_field['name'] ] );
      return $value;
    }

    return $output;
  }


  /**
   * Resolve merge tags on the form {field:FIELD_NAME} to render a single field value.
   *
   * @since 1.6.0
   *
   */
  function resolve_field_tag( $output, $tag, $fields ) {
    if ( ! empty( $output ) || ! preg_match_all( '/field:(.*)/', $tag, $matches ) ) {
      return $output;
    }

    $field_name = $matches[1][0];
    $field = af_get_field_object( $field_name, $fields );
    $rendered_value = _af_render_field_include( $field );

    return $rendered_value;
  }

}

return new AF_Core_Merge_Tags();