<?php

class AF_Admin_Restrictions {
	
	function __construct() {
		
		// Filters
		add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 10, 1 );
		
	}
	
	
	/**
	 * Add form settings for restrictions
	 *
	 * @since 1.0.3
	 */
	function add_form_settings_fields( $field_group ) {
		
		$field_group['fields'][] = array(
			'key' => 'field_form_restrictions_tab',
			'label' => '<span class="dashicons dashicons-dismiss"></span>' . __( 'Restrictions', 'advanced-forms' ),
			'name' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'left',
			'endpoint' => 0,
		);
		
		// Entry limits
		$field_group['fields'][] = array(
			'key' => 'field_form_restrict_entries',
			'label' => __( 'Restrict number of entries', 'advanced-forms' ),
			'name' => 'form_restrict_entries',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'placeholder' => '',
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'ui' => true,
			'default_value' => 0,
		);
		
		$field_group['fields'][] = array(
			'key' => 'field_form_max_entries',
			'label' => __( 'Maximum number of entries', 'advanced-forms' ),
			'name' => 'form_max_entries',
			'type' => 'number',
			'instructions' => '',
			'required' => 1,
			'placeholder' => '',
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_restrict_entries',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'ui' => true,
			'default_value' => 0,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_entry_restriction_message',
			'label' => __( 'Message after entry limit has been reached', 'advanced-forms' ),
			'name' => 'form_entry_restriction_message',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_restrict_entries',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'rows' => 4,
			'default_value' => '',
		);
		
		// User logged in
		$field_group['fields'][] = array(
			'key' => 'field_form_require_login',
			'label' => __( 'Require user to be logged in', 'advanced-forms' ),
			'name' => 'form_require_login',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'placeholder' => '',
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'ui' => true,
			'default_value' => 0,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_login_restriction_message',
			'label' => __( 'Message if user is not logged in', 'advanced-forms' ),
			'name' => 'form_login_restriction_message',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_require_login',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'rows' => 4,
			'default_value' => '',
		);
		
		// Scheduled form
		$field_group['fields'][] = array(
			'key' => 'field_form_schedule_form',
			'label' => __( 'Only display form during a specific time', 'advanced-forms' ),
			'name' => 'form_schedule_form',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'placeholder' => '',
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'ui' => true,
			'default_value' => 0,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_schedule_start',
			'label' => __( 'Start time', 'advanced-forms' ),
			'name' => 'form_schedule_start',
			'type' => 'date_time_picker',
			'display_format' => 'd/m/Y g:i a',
			'return_format' => 'Y-m-d H:i:s',
			'first_day' => 1,
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_schedule_form',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_schedule_end',
			'label' => __( 'End time', 'advanced-forms' ),
			'name' => 'form_schedule_end',
			'type' => 'date_time_picker',
			'display_format' => 'd/m/Y g:i a',
			'return_format' => 'Y-m-d H:i:s',
			'first_day' => 1,
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_schedule_form',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
		);
		
		$field_group['fields'][] = array(
			'key' => 'field_form_before_schedule_message',
			'label' => __( 'Message before scheduled time', 'advanced-forms' ),
			'name' => 'form_before_schedule_message',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_schedule_form',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'rows' => 4,
			'default_value' => '',
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_after_schedule_message',
			'label' => __( 'Message after scheduled time', 'advanced-forms' ),
			'name' => 'form_after_schedule_message',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_form_schedule_form',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'rows' => 4,
			'default_value' => '',
		);


		$field_group = apply_filters( 'af/form/restriction_settings_fields', $field_group );
		
		return $field_group;
		
	}
	
}

return new AF_Admin_Restrictions();