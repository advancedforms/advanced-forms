<?php
	
class AF_Admin_Emails {
	
	function __construct() {
		
		add_filter( 'af/form/settings_fields', array( $this, 'email_acf_fields' ), 10, 1 );
		
	}
	
	
	/**
	 * Add fields for setting up emails to the form settings
	 *
	 * @since 1.0.0
	 *
	 */
	function email_acf_fields( $field_group ) {
		
		$field_group['fields'][] = array (
			'key' => 'field_form_email_tab',
			'label' => 'Email',
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
		
		$field_group['fields'][] = array (
			'key' => 'field_form_emails',
			'label' => 'Emails',
			'name' => 'form_emails',
			'type' => 'repeater',
			'instructions' => 'The emails defined here will be sent upon successful submission.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => 'field_form_email_name',
			'min' => '',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add new email',
			'sub_fields' => array (
				array (
					'key' => 'field_form_email_name',
					'label' => 'Name',
					'name' => 'name',
					'type' => 'text',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '66',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 'Name this email for your reference',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_form_email_active',
					'label' => 'Active?',
					'name' => 'active',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '33',
						'class' => '',
						'id' => '',
					),
					'message' => 'Yes',
					'default_value' => 1,
				),
				array (
					'key' => 'field_form_email_recipient_type',
					'label' => 'Recipient',
					'name' => 'recipient_type',
					'type' => 'radio',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'field' => 'Select from field',
						'custom' => 'Custom recipient',
					),
					'allow_null' => 0,
					'other_choice' => 0,
					'save_other_choice' => 0,
					'default_value' => '',
					'layout' => 'horizontal',
					'return_format' => 'value',
				),
				array (
					'key' => 'field_form_email_recipient_field',
					'label' => 'Recipient from field',
					'name' => 'recipient_field',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_form_email_recipient_type',
								'operator' => '==',
								'value' => 'field',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
					),
					'default_value' => array (
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'return_format' => 'value',
					'placeholder' => '',
				),
				array (
					'key' => 'field_form_email_recipient_custom',
					'label' => 'Custom recipient',
					'name' => 'recipient_custom',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_form_email_recipient_type',
								'operator' => '==',
								'value' => 'custom',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_form_email_subject',
					'label' => 'Subject',
					'name' => 'subject',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_form_email_subject',
					'label' => 'Content',
					'name' => 'content',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
			),
		);
		
		
		return $field_group;
		
	}
	
}

new AF_Admin_Emails();