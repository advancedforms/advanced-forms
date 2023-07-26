<?php

/**
 * Handles rendering of forms.
 * Refactored out of core-forms.php since 1.5.0.
 *
 * @since 1.5.0
 */
class AF_Core_Forms_Rendering {

	function __construct() {
		add_shortcode( 'advanced_form', array( $this, 'form_shortcode' ) );
		add_action( 'af/form/render', array( $this, 'render' ), 10, 2 );
	}

	/**
	 * Registers the shortcode advanced_form which renders the form specified by the "form" attribute
	 *
	 * @since 1.0.0
	 */
	function form_shortcode( $atts ) {
		if ( isset( $atts['form'] ) ) {

			$form_id_or_key = $atts['form'];
			unset( $atts['form'] );

			ob_start();

			$this->render( $form_id_or_key, $atts );

			$output = ob_get_clean();

			return $output;
		}
	}

	/**
	 * Renders the form specified by ID
	 *
	 * @since 1.0.0
	 */
	function render( $form_id_or_key, $args ) {
		$form = af_get_form( $form_id_or_key );

		if ( ! $form ) {
			return;
		}

		// Enqueue scripts and styles
		af_enqueue();

		do_action( 'af/form/enqueue', $form, $args );
		do_action( 'af/form/enqueue/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/enqueue/key=' . $form['key'], $form, $args );

		// Allow the form to be modified before rendering form
		$form = apply_filters( 'af/form/before_render', $form, $args );
		$form = apply_filters( 'af/form/before_render/id=' . $form['post_id'], $form, $args );
		$form = apply_filters( 'af/form/before_render/key=' . $form['key'], $form, $args );

		$args = wp_parse_args( $args, array(
			'ajax' => false,
			'display_title' => false,
			'display_description' => false,
			'id' => $form['key'],
			'values' => array(),
			'submit_text' => __( 'Submit', 'advanced-forms' ),
			'redirect' => null,
			'target' => acf_get_current_url(),
			'echo' => true,
			'exclude_fields' => array(),
			'uploader' => 'wp',
			'filter_mode' => false,
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'honeypot' => true,
		) );

		// Allow the arguments to be modified before rendering form
		$args = apply_filters( 'af/form/args', $args, $form );
		$args = apply_filters( 'af/form/args/id=' . $form['post_id'], $args, $form );
		$args = apply_filters( 'af/form/args/key=' . $form['key'], $args, $form );

		// Allow a comma separated string for excluded fields
		if ( is_string( $args['exclude_fields'] ) ) {
			$args['exclude_fields'] = explode( ',', $args['exclude_fields'] );
		}

		// Set ACF uploader type setting
		acf_update_setting( 'uploader', $args['uploader'] );

		// Form element
		$form_attributes = array(
			'class' => 'af-form acf-form',
			'method' => 'POST',
			'action' => $args['target'],
			'id' => $args['id'],
			'data-key' => $form['key'],
		);

		// Add data attribute to indicate that the form should use AJAX submissions
		if ( $args['ajax'] ) {
			$form_attributes['data-ajax'] = true;
		}

		$form_attributes = apply_filters( 'af/form/attributes', $form_attributes, $form, $args );
		$form_attributes = apply_filters( 'af/form/attributes/id=' . $form['post_id'], $form_attributes, $form, $args );
		$form_attributes = apply_filters( 'af/form/attributes/key=' . $form['key'], $form_attributes, $form, $args );

		echo sprintf( '<form %s>', acf_esc_atts( $form_attributes ) );

		do_action( 'af/form/before_title', $form, $args );
		do_action( 'af/form/before_title/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/before_title/key=' . $form['key'], $form, $args );

		// Render submission error message if one exists
		$this->render_submission_error( $form, $args );

		// Render title and description if they should be visible
		$this->render_title_and_description( $form, $args );

		/**
		 * Check if form should be restricted and not displayed.
		 * Filter will return false if no restriction is applied otherwise it will return a string to display.
		 */
		$restriction = false;
		$restriction = apply_filters( 'af/form/restriction', $restriction, $form, $args );
		$restriction = apply_filters( 'af/form/restriction/id=' . $form['post_id'], $restriction, $form, $args );
		$restriction = apply_filters( 'af/form/restriction/key=' . $form['key'], $restriction, $form, $args );

		// Display success message, restriction message, or fields
		$instance_hash = af_form_instance_hash( $form['key'], $args );
		if ( af_has_submission( $instance_hash ) && ! af_submission_failed( $form['key'] ) && ! $args['filter_mode'] ) {

			echo af_form_success_message( $form, $args );

		} elseif ( $restriction ) {

			$this->render_restriction_message( $restriction );

		} else {

			$this->render_fields( $form, $args );

		}

		// End form
		echo '</form>';

		do_action( 'af/form/after', $form, $args );
		do_action( 'af/form/after/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/after/key=' . $form['key'], $form, $args );
	}

	/**
	 * Renders all submission errors if any exist.
	 *
	 * @since 1.6.0
	 */
	function render_submission_error( $form, $args ) {
		if ( af_submission_failed( $form['key'] ) ) {
			$errors = AF()->submission['errors'];

			foreach ( $errors as $error ) {
				echo '<div class="acf-notice -error -acf-error-message-dismiss">';
				echo sprintf( '<p>%s</p>', $error );
				echo '</div>';
			}
		}
	}

	/**
	 * Renders title and description of form if they should be shown.
	 *
	 * @since 1.6.0
	 */
	function render_title_and_description( $form, $args ) {
		// Display title
		if ( $args['display_title'] ) {
			echo sprintf( '<h1 class="af-title">%s</h1>', $form['title'] );
		}

		// Display description
		if ( $args['display_description'] ) {
			echo sprintf( '<div class="af-description">%s</div>', $form['display']['description'] );
		}
	}

	/**
	 * Renders the restriction message for a form.
	 *
	 * @since 1.6.0
	 */
	function render_restriction_message( $message ) {
		echo '<div class="af-restricted-message">';
		echo $message;
		echo '</div>';
	}

	/**
	 * Renders a field wrapper with all fields and a submit button.
	 *
	 * @since 1.6.0
	 */
	function render_fields( $form, $args ) {
		// Increment the form view counter
		$view_counter_enabled = true;
		$view_counter_enabled = apply_filters( 'af/form/view_counter_enabled', $view_counter_enabled, $form, $args );
		$view_counter_enabled = apply_filters( 'af/form/view_counter_enabled/id=' . $form['post_id'], $view_counter_enabled, $form, $args );
		$view_counter_enabled = apply_filters( 'af/form/view_counter_enabled/key=' . $form['key'], $view_counter_enabled, $form, $args );
		if ( $form['post_id'] && ! $args['filter_mode'] && $view_counter_enabled ) {
			$views = get_post_meta( $form['post_id'], 'form_num_of_views', true );
			$views = $views ? $views + 1 : 1;
			update_post_meta( $form['post_id'], 'form_num_of_views', $views );
		}

		// Get field groups for the form and display their fields
		$field_groups = af_get_form_field_groups( $form['key'] );

		do_action( 'af/form/before_field_wrapper', $form, $args );
		do_action( 'af/form/before_field_wrapper/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/before_field_wrapper/key=' . $form['key'], $form, $args );

		echo sprintf( '<div class="af-fields acf-fields acf-form-fields -%s">', $args['label_placement'] );

		do_action( 'af/form/before_fields', $form, $args );
		do_action( 'af/form/before_fields/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/before_fields/key=' . $form['key'], $form, $args );

		// Form data required by ACF for validation to work.
		$acf_form_data = array(
			'screen' => 'acf_form',
			'post_id' => false,
			'form' => false,
		);

		$acf_form_data = apply_filters( 'af/form/acf_data', $acf_form_data, $form, $args );
		$acf_form_data = apply_filters( 'af/form/acf_data/id=' . $form['post_id'], $acf_form_data, $form, $args );
		$acf_form_data = apply_filters( 'af/form/acf_data/key=' . $form['key'], $acf_form_data, $form, $args );

		acf_form_data( $acf_form_data );

		// Hidden fields to identify form
		echo '<div class="acf-hidden">';

		$acf_nonce = wp_create_nonce( 'acf_nonce' );
		echo sprintf( '<input type="hidden" name="_acfnonce" value="%s">', $acf_nonce );
		echo sprintf( '<input type="hidden" name="nonce" value="%s">', $acf_nonce );

		echo sprintf( '<input type="hidden" name="af_form" value="%s">', $form['key'] );

		$encoded_args = base64_encode( json_encode( $args ) );
		echo sprintf( '<input type="hidden" name="af_form_args" value="%s">', $encoded_args );
		echo sprintf( '<input type="hidden" name="_acf_form" value="%s">', $encoded_args );

		// Add nonce to ensure arguments can't be altered.
		$hashed_args = hash( 'sha256', $encoded_args );
		$nonce = wp_create_nonce( sprintf( 'af_submission_%s_%s', $form['key'], $hashed_args ) );
		echo sprintf( '<input type="hidden" name="af_form_nonce" value="%s">', $nonce );

		// Add honeypot field that is not visible to users.
		// Bots should hopefully fill this in allowing them to be detected.
		if ( $args['honeypot'] ) {
			echo sprintf( '<label for="af_email_for_non_humans_%s" aria-hidden="true">Email for non-humans</label>', $form['key'] );
			echo sprintf( '<input type="text" name="email_for_non_humans" id="af_email_for_non_humans_%s" tabindex="-1" autocomplete="off" />', $form['key'] );
		}

		// Add origin URL to enable an automatic redirect back to the form page after submission.
		echo sprintf( '<input type="hidden" name="af_origin_url" value="%s" />', esc_attr( acf_get_current_url() ) );

		do_action( 'af/form/hidden_fields', $form, $args );
		do_action( 'af/form/hidden_fields/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/hidden_fields/key=' . $form['key'], $form, $args );

		echo '</div>';

		foreach ( $field_groups as $field_group ) {
			$this->render_field_group( $field_group, $form, $args );
		}

		do_action( 'af/form/after_fields', $form, $args );
		do_action( 'af/form/after_fields/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/after_fields/key=' . $form['key'], $form, $args );

		$this->render_submit_button( $form, $args );

		// End fields wrapper
		echo '</div>';

		do_action( 'af/form/after_field_wrapper', $form, $args );
		do_action( 'af/form/after_field_wrapper/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/after_field_wrapper/key=' . $form['key'], $form, $args );
	}

	/**
	 * Renders a full field group with all fields that are not excluded.
	 *
	 * @since 1.6.0
	 */
	function render_field_group( $field_group, $form, $args ) {
		do_action( 'af/field_group/before_field_group', $field_group, $form, $args );

		// Get all fields for field group
		$fields = acf_get_fields( $field_group );

		foreach ( $fields as $field ) {

			// Skip field if it is in the exluded fields argument
			if ( isset( $args['exclude_fields'] ) && is_array( $args['exclude_fields'] ) ) {

				if ( in_array( $field['key'], $args['exclude_fields'] ) || in_array( $field['name'], $args['exclude_fields'] ) ) {
					continue;
				}

			}

			$this->render_field( $field, $form, $args );
		}

		do_action( 'af/field_group/after_field_group', $field_group, $form, $args );
	}

	/**
	 * Renders a single field as part of a form.
	 *
	 * @since 1.5.0
	 */
	function render_field( $field, $form, $args ) {
		// Ignore hide from admin value
		$field['hide_admin'] = false;

		// Include default value
		if ( empty( $field['value'] ) && isset( $field['default_value'] ) ) {
			$field['value'] = $field['default_value'];
		}

		// Include pre-fill values (either through args or filter)
		if ( isset( $args['values'][ $field['name'] ] ) ) {
			$field['value'] = $args['values'][ $field['name'] ];
		}

		if ( isset( $args['values'][ $field['key'] ] ) ) {
			$field['value'] = $args['values'][ $field['key'] ];
		}

		$field['value'] = apply_filters( 'af/field/prefill_value', $field['value'], $field, $form, $args );
		$field['value'] = apply_filters( 'af/field/prefill_value/name=' . $field['name'], $field['value'], $field, $form, $args );
		$field['value'] = apply_filters( 'af/field/prefill_value/key=' . $field['key'], $field['value'], $field, $form, $args );

		// Include any previously submitted value
		if ( isset( $_POST['acf'][ $field['key'] ] ) ) {
			$field['value'] = $_POST['acf'][ $field['key'] ];
		}

		// If the previous submission failed or filter mode is enabled, the submitted field values should be reused
		$instance_hash = af_form_instance_hash( $form['key'], $args );
		if ( af_has_submission( $instance_hash ) && ( $args['filter_mode'] || af_submission_failed( $form['key'] ) ) ) {
			$field['value'] = af_get_field( $field['name'] );
		}

		$field = apply_filters( 'af/field/before_render', $field, $form, $args );
		$field = apply_filters( 'af/field/before_render/name=' . $field['name'], $field, $form, $args );
		$field = apply_filters( 'af/field/before_render/key=' . $field['key'], $field, $form, $args );

		// Allow "af/field/before_render" to remove a field by returning false
		if ( ! $field ) {
			return;
		}

		// Attributes to be used on the wrapper element
		$attributes = array();

		if ( ! empty( $field['wrapper']['id'] ) ) {
			$attributes['id'] = $field['wrapper']['id'];
		}

		$attributes['class'] = $field['wrapper']['class'];

		$attributes['class'] .= sprintf( ' af-field af-field-type-%s af-field-%s acf-field acf-field-%s acf-field-%s', $field['type'], $field['name'], $field['type'], $field['key'] );

		if ( $field['required'] ) {
			$attributes['class'] .= ' af-field-required';
		}

		// This is something ACF needs
		$attributes['class'] = str_replace( '_', '-', $attributes['class'] );
		$attributes['class'] = str_replace( 'field-field-', 'field-', $attributes['class'] );

		$width = $field['wrapper']['width'];

		if ( $width ) {
			$attributes['data-width'] = $width;
			$attributes['style'] = 'width: ' . $width . '%;';
		}

		$attributes['data-name'] = $field['name'];
		$attributes['data-key'] = $field['key'];
		$attributes['data-type'] = $field['type'];

		if ( ! empty( $field['conditional_logic'] ) ) {
			$field['conditions'] = $field['conditional_logic'];
		}

		if ( ! empty( $field['conditions'] ) ) {
			$attributes['data-conditions'] = $field['conditions'];
		}

		$attributes = apply_filters( 'af/form/field_attributes', $attributes, $field, $form, $args );
		$attributes = apply_filters( 'af/form/field_attributes/id=' . $form['post_id'], $attributes, $field, $form, $args );
		$attributes = apply_filters( 'af/form/field_attributes/key=' . $form['key'], $attributes, $field, $form, $args );

		// Field instructions
		$instruction_placement = $args['instruction_placement'];
		$instruction_placement = apply_filters( 'af/field/instruction_placement', $instruction_placement, $field, $form, $args );
		$instruction_placement = apply_filters( 'af/field/instruction_placement/name=' . $field['name'], $instruction_placement, $field, $form, $args );
		$instruction_placement = apply_filters( 'af/field/instruction_placement/key=' . $field['key'], $instruction_placement, $field, $form, $args );

		if ( ! empty( $field['instructions'] ) ) {
			$instructions = sprintf( '<p class="af-field-instructions -placement-%s">%s</p>', $instruction_placement, $field['instructions'] );
		} else {
			$instructions = '';
		}

		do_action( 'af/field/before_field', $field, $form, $args );
		do_action( 'af/field/before_field/name=' . $field['name'], $field, $form, $args );
		do_action( 'af/field/before_field/key=' . $field['key'], $field, $form, $args );

		// Field wrapper
		echo sprintf( '<div %s>', acf_esc_atts( $attributes ) );

		echo '<div class="af-label acf-label">';

		$label = $field['label'];

		$label .= $field['required'] ? ' <span class="acf-required">*</span>' : '';

		echo sprintf( '<label for="acf-%s">%s</label>', $field['key'], $label );

		if ( 'label' == $instruction_placement ) {
			echo $instructions;
		}

		echo '</div>';

		echo '<div class="af-input acf-input">';

		// Render field with default ACF
		acf_render_field( $field );

		echo '</div>';

		if ( 'field' == $instruction_placement ) {
			echo $instructions;
		}

		// End field wrapper
		echo '</div>';

		do_action( 'af/field/after_field', $field, $form, $args );
		do_action( 'af/field/after_field/name=' . $field['name'], $field, $form, $args );
		do_action( 'af/field/after_field/key=' . $field['key'], $field, $form, $args );
	}

	function render_submit_button( $form, $args ) {
		// Submit button and loading indicator
		$button_attributes = array();

		$button_attributes['class'] = 'acf-button af-submit-button';

		$button_attributes = apply_filters( 'af/form/button_attributes', $button_attributes, $form, $args );
		$button_attributes = apply_filters( 'af/form/button_attributes/id=' . $form['post_id'], $button_attributes, $form, $args );
		$button_attributes = apply_filters( 'af/form/button_attributes/key=' . $form['key'], $button_attributes, $form, $args );

		echo '<div class="af-submit acf-form-submit">';
		echo sprintf( '<button type="submit" %s>%s</button>', acf_esc_atts( $button_attributes ), $args['submit_text'] );
		echo '<span class="acf-spinner af-spinner"></span>';
		echo '</div>';
	}

}

return new AF_Core_Forms_Rendering();