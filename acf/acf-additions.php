<?php
	
	
/**
 * This class wraps all integrations with ACF such as custom location rules and fields
 *
 * @since 1.0.0
 *
 */
class AF_ACF_Additions {
	
	function __construct() {
		
		add_filter( 'acf/location/rule_types', array( $this, 'add_form_location_type' ), 10, 1 );
		add_filter( 'acf/location/rule_values/af_form', array( $this, 'form_location_rule_values' ), 10, 1 );
		add_filter( 'acf/location/rule_match/af_form', array( $this, 'form_location_rule_match' ), 10, 3 );
		
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
			
			if ( $rule['value'] == $options['af_form'] ) {
				
				$match = true;
				
			}
			
		}
		
		// Match with entry
		if ( 'af_entry' == $options['post_type'] ) {
			
			$entry_form = get_post_meta( $options['post_id'], 'entry_form', true );
			
			if ( $entry_form && $entry_form == $rule['value'] ) {
				
				$match = true;
				
			}
			
		}
		
		return $match;
		
	}
	
}

new AF_ACF_Additions();