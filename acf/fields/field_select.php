<?php

/**
 * Based on the default ACF select field
 *
 * @since 1.3.2
 *
 */

if( ! class_exists('AF_Form_Field_Picker') ) :

/**
 * Custom private ACF field used for picking a form field.
 *
 * @since 1.3.2
 *
 */
class AF_Form_Field_Picker extends acf_field {
	
	
	/**
	 * Set up field defaults
	 *
	 * @since 1.3.2
	 *
	 */
	function __construct() {
		
		// vars
		$this->name = 'field_picker';
		$this->label = _x('Field picker', 'noun', 'acf');
		$this->public = false;
		$this->defaults = array(
			'allow_null' 	=> 0,
			'allow_custom'	=> 0,
			'field_types'	=> 'regular',
			'choices'		=> array(),
			'default_value'	=> array(
				'field' => false,
				'format' => false,
			),
			'placeholder'	=> '',
		);
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/**
	 * Render interface for field
	 *
	 * @since 1.3.2
	 *
	 */
	function render_field( $field ) {
		
		global $post;
		
		$field['value'] = wp_parse_args( $field['value'], array(
			'field' => false,
			'format' => false,
		));
		
		
		if ( $field['allow_custom'] ) {
			$field['choices']['custom'] = 'Custom format';
		}
		
		if ( $post && $form_key = get_post_meta( $post->ID, 'form_key', true ) ) {
			
			$field['choices']['Fields'] = _af_form_field_choices( $form_key, $field['field_types'] );
			
		}
		
		
		// placeholder
		if( empty($field['placeholder']) ) {
		
			$field['placeholder'] = _x('Select', 'verb', 'acf');
			
		}
		
		
		// add empty value (allows '' to be selected)
		if( empty($field['value']) ) {
			
			$field['value'] = array('');
			
		}
		
		
		// allow null
		// - have tried array_merge but this causes keys to re-index if is numeric (post ID's)
		if ( $field['allow_null'] ) {
			
			$prepend = array(''	=> '- ' . $field['placeholder'] . ' -');
			$field['choices'] = $prepend + $field['choices'];
			
		}
		
		
		// vars
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> $field['class'],
			'name'				=> $field['name'] . '[field]',
			'data-placeholder'	=> $field['placeholder'],
			'data-allow_null'	=> $field['allow_null']
		);
		
		
		// special atts
		foreach( array( 'readonly', 'disabled' ) as $k ) {
		
			if( !empty($field[ $k ]) ) $atts[ $k ] = $k;
			
		}
		
		
		
		// open
		echo '<select ' . acf_esc_atts($atts) . '>';	
		
		
		// walk
		$this->walk( $field['choices'], $field['value']['field'] );
		
		
		// close
		echo '</select>';
		
		if ( $field['allow_custom'] ) {
			
			echo '<div class="acf-input format-input" style="display: none; margin-top: 10px;">';
			
			$format_atts = array(
				'type' => 'text',
				'name' => $field['name'] . '[format]',
				'value' => $field['value']['format'] ?: '',
			);
			
			echo '<input ' . acf_esc_atts( $format_atts ) . '/>';
			
			_af_field_inserter_button( $form_key, 'all', true );
			
			echo '</div>';
			
		}
		
	}
	
	
	/**
	 * Walk and output select options
	 *
	 * @since 1.3.2
	 *
	 */
	function walk( $choices, $value ) {
		
		// bail ealry if no choices
		if( empty($choices) ) return;
		
		
		// loop
		foreach( $choices as $k => $v ) {
			
			// optgroup
			if( is_array($v) ){
				
				// optgroup
				echo '<optgroup label="' . esc_attr($k) . '">';
				
				
				// walk
				$this->walk( $v, $value );
				
				
				// close optgroup
				echo '</optgroup>';
				
				
				// break
				continue;
				
			}
			
			
			// vars
			$search = html_entity_decode($k);
			$atts = array( 'value' => $k );
			
			
			// validate selected
			if( $search == $value ) {
				
				$atts['selected'] = 'selected';
				
			}
			
			
			// option
			echo '<option ' . acf_esc_atts($atts) . '>' . $v . '</option>';
			
		}
		
	}
	
	
	function validate_value( $valid, $value, $field, $input ) {
		
		if ( empty( $value['field'] ) && $field['required'] ) {
			return sprintf( __( '%s value is required', 'acf' ), $field['label'] );
		}
		
		return $valid;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) return false;
		
		
		// return
		return $value;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// validate
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @type	function
	*  @date	8/03/2016
	*  @since	5.3.2
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		// translate
		$field['choices'] = acf_translate( $field['choices'] );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		$value = wp_parse_args( $value, array(
			'field' => false,
			'format' => false,
		));
		
		// return
		return $value;
		
	}
	
}


// initialize
acf_register_field_type( new AF_Form_Field_Picker() );

endif; // class_exists check

?>