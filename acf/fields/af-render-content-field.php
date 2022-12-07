<?php

/**
 * A custom private ACF field used to render the results of a callback passed to it. This is merely a convenient wrapper
 * that allows us to render any type of markeup we like when building the Advanced Forms UI. It's particularly useful
 * when we want to set up sub fields on a repeater or flexi-content field and have those sub fields render display
 * markup or some arbitrary field using acf_render_field_wrap().
 *
 * Note, if `label` AND `instructions` are both empty, the containing label element will be hidden by CSS.
 *
 * Usage example — passing callback as a value:
 *  [
 *      'key' => 'field_some_field_key',
 *      'type' => 'af_render_content',
 *      'name' => 'some_field_name',
 *      'label' => 'Some label here',
 *      'instructions' => 'Some instructions go here',
 *      'value' => function( $field ) {
 *          echo 'The content here...';
 *          acf_render_field_wrap([
 *              'label' => 'Test',
 *              'type' => 'textarea',
 *              'name' => 'test',
 *          ]);
 *      },
 *  ]
 *
 * Usage example — passing callback as value parameter:
 *  [
 *      'key' => 'field_some_field_key',
 *      'type' => 'af_render_content',
 *      'name' => 'some_field_name',
 *      'label' => 'Some label here',
 *      'instructions' => 'Some instructions go here',
 *      'value' => [
 *          'some' => 'data',
 *          'render_function' => function( $field ) {
 *              echo 'The content here...';
 *              acf_render_field_wrap([
 *                  'label' => 'Test',
 *                  'type' => 'textarea',
 *                  'name' => 'test',
 *              ]);
 *          },
 *      ],
 *  ]
 *
 * Usage example — passing callback as field arg:
 *  [
 *      'key' => 'field_some_field_key',
 *      'type' => 'af_render_content',
 *      'name' => 'some_field_name',
 *      'label' => 'Some label here',
 *      'instructions' => 'Some instructions go here',
 *      'render_function' => function( $field ) {
 *          echo 'The content here...';
 *          acf_render_field_wrap([
 *              'label' => 'Test',
 *              'type' => 'textarea',
 *              'name' => 'test',
 *          ]);
 *      },
 *  ]
 */
class AF_Render_Content_Field extends acf_field {

	function __construct() {
		$this->name = 'af_render_content';
		$this->label = _x( 'Render', 'noun', 'advanced-forms' );
		$this->public = false;
		$this->defaults = [];
		$this->category = 'Forms';
		parent::__construct();
		add_filter( 'acf/field_wrapper_attributes', [ $this, '_add_hidden_label_class' ], 10, 3 );
	}

	function render_field( $field ) {

		// First, check to see if the field value is callable.
		// Next, check to see if the field value has a 'render_function' key with a callable assigned.
		// Finally, check to see if the field array has a 'render_function' key with a callable assigned.
		// Call the first callable found. This allows a lot of flexibility, particularly when using repeaters.
		if ( ! empty( $field['value'] ) && is_callable( $field['value'] ) ) {
			call_user_func( $field['value'], $field );

		} else if ( ! empty( $field['value']['render_function'] ) && is_callable( $field['value']['render_function'] ) ) {
			call_user_func( $field['value']['render_function'], $field );

		} else if ( ! empty( $field['render_function'] ) && is_callable( $field['render_function'] ) ) {
			call_user_func( $field['render_function'], $field );
		}
	}

	public function _add_hidden_label_class( $wrapper, $field ) {
		if ( $field['type'] !== $this->name ) {
			return $wrapper;
		}

		// This could use further refinement to cover the various scenarios around description placement, etc.
		if ( empty( $field['label'] ) and empty( $field['instructions'] ) ) {
			$wrapper['class'] .= ' af-field-hide-label';
		}

		return $wrapper;
	}

}

acf_register_field_type( new AF_Render_Content_Field() );