<?php
	
	
/**
 * This class wraps all integrations with ACF such as custom location rules and fields
 *
 * @since 1.0.0
 *
 */
class AF_ACF_Additions {
	
	function __construct() {
		
		add_action('acf/render_field_settings', array( $this, 'field_settings'), 100 );
		add_action('acf/prepare_field', array( $this, 'hide_field_from_admin'), 100 );
		
		add_filter( 'acf/location/rule_types', array( $this, 'add_form_location_type' ), 10, 1 );
		add_filter( 'acf/location/rule_values/af_form', array( $this, 'form_location_rule_values' ), 10, 1 );
		add_filter( 'acf/location/rule_match/af_form', array( $this, 'form_location_rule_match' ), 10, 3 );
		
	}
	
	
	/**
	 * Add extra global field settings
	 *
	 * @since 1.3.1
	 *
	 */
	function field_settings( $field ) {
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Hide from admin?'),
			'instructions'	=> 'Only show this field in forms',
			'name'			=> 'hide_admin',
			'type'			=> 'true_false',
			'ui'			=> 1,
		), true);
		
	}
	
	
	/**
	 * Hide fields with the "Hide from admin?" setting
	 *
	 * @since 1.3.1
	 *
	 */ 
	function hide_field_from_admin( $field ) {
		
		if ( empty( $field['hide_admin'] ) ) {
			return $field;
		}
		
		
		if ( $field['hide_admin'] && is_admin() ) {
			return false;
		}
		
		
		return $field;
		
	}
	
	
	/**
	 * Adds a new location rule type "Form" to ACF
	 *
	 * @since 1.0.0
	 *
	 */
	function add_form_location_type( $choices ) {
		
		$choices['Advanced Forms']['af_form'] = 'Form';
		
		return $choices;
		
	}
	
	
	/**
	 * Populates the choices for the location rule type "Form"
	 *
	 * @since 1.0.0
	 *
	 */
	function form_location_rule_values( $choices ) {
		
		$forms = af_get_forms();
		
		foreach ( $forms as $form ) {
			$choices[ $form['key'] ] = $form['title'];
		}
		
		return $choices;
		
	}
	
	
	function form_location_rule_match( $match, $rule, $options ) {
		
		
		// Match with form object
		if ( 'af_form' == $rule['param'] && isset( $options['af_form'] ) ) {
			
			if( isset( $rule['value'] ) && $rule['value'] == $options['af_form'] ) {
				
				$match = true;
				
			}
			
		}
		
		
		// Match with entry
		if ( isset( $options['post_type'] ) && 'af_entry' == $options['post_type'] ) {
			
			$entry_form = get_post_meta( $options['post_id'], 'entry_form', true );
			
			if ( $entry_form && $entry_form == $rule['value'] ) {
				
				$match = true;
				
			}
			
		}
		
		
		return $match;
		
	}
	
}

return new AF_ACF_Additions();