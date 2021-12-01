<?php

class AF_Core_Gutenberg {
	function __construct() {
		add_action( 'acf/init', array( $this, 'register_block'));
		add_filter( 'acf/load_field/name=af_block_form', array( $this, 'populate_form_select_field'), 10, 1 );
		add_filter( 'acf/load_field/name=af_block_exclude_fields', array( $this, 'populate_exclude_fields_field'), 10, 1 );

		add_action( 'acf/init', array( $this, 'register_block_fields' ) );
		add_filter( 'af/form/gutenberg/fields', array( $this, 'add_general_block_settings' ), 10, 1 );

		add_action( 'wp_ajax_af_gutenberg_get_form_data', array( $this, 'ajax_get_form_data' ), 10, 0 );
	}

	function register_block() {
		acf_register_block_type(array(
            'name'              => 'advanced-form',
            'title'             => __( 'Advanced Form', 'advanced-forms' ),
            'description'       => __( 'Include a form from Advanced Forms' ),
			'render_callback' 	=> array( $this, 'render' ),
			'enqueue_assets' 	=> 'af_enqueue',
            'category'          => 'formatting',
            'icon'              => 'list-view',
        ));
	}

	function render( $block, $content, $is_preview ) {
		$form_key = get_field( 'af_block_form' );
		if ( ! $form_key  ) {
			?>
			<div class="af-block-preview">
				<div class="af-block-no-form"><?php _e( 'No form selected', 'advanced-forms' ); ?></div>
			</div>
			<?php	
			return;
		}

		$form = af_get_form( $form_key );

		// The form can't be previewed in the Gutenberg editor as ACF will try to validate and save the
		// fields in the form as if they were fields on the post. Instead we show a placeholder.
		if ( $is_preview ) {
			?>
			<div class="af-block-preview">
				<div class="af-block-description"><?php _e( 'Form', 'advanced-forms' ); ?></div>
				<div class="af-block-form-name"><?php echo $form['title']; ?></div>
				<div class="af-block-subtitle"><?php _e( 'Preview page to view form', 'advanced-forms' ); ?></div>
			</div>
			<?php
			return;
		}

		$args = array(
			'ajax' => get_field( 'af_block_ajax' ),
		);

		if ( $submit_text = get_field( 'af_block_submit_text' ) ) {
			$args['submit_text'] = $submit_text;
		}

		switch ( get_field( 'af_block_after_submission' ) ) {
			case 'redirect_link':
				$args['redirect'] = get_field( 'af_block_redirect_to_link' ); 
				break;
			case 'redirect_url':
				$args['redirect'] = get_field( 'af_block_redirect_to_url' ); 
				break;
		}

		if ( $excluded_fields = get_field( 'af_block_exclude_fields' ) ) {
			$args['exclude_fields'] = $excluded_fields;
		}

		$args =	apply_filters( 'af/form/gutenberg/args', $args, $form );

		advanced_form( $form_key, $args );
	}

	function register_block_fields() {
		$fields = apply_filters( 'af/form/gutenberg/fields', array() );

		acf_add_local_field_group(array(
			'key' => 'group_af_block',
			'title' => 'Advanced Forms Gutenberg Block',
			'fields' => $fields,
			'location' => array(
				array(
					array(
						'param' => 'block',
						'operator' => '==',
						'value' => 'acf/advanced-form',
					),
				),
			),
		));
	}

	function add_general_block_settings( $fields ) {
		$fields[] = array(
			'key' => 'field_af_block_tab_general',
			'label' => __( 'General', 'advanced-form' ),
			'name' => 'af_block_tab_general',
			'type' => 'tab',
		);

		$fields[] = array(
			'key' => 'field_af_block_form',
			'label' => __( 'Form', 'advanced-form' ),
			'name' => 'af_block_form',
			'instructions' => sprintf(
				'<a class="edit-form-link">%s &rarr;</span></a>',
				__( 'Edit form settings', 'advanced-forms' )
			),
			'type' => 'select',
			'ui' => 1,
			'return_format' => 'value',
		);

		$fields[] = array(
			'key' => 'field_af_block_ajax',
			'label' => __( 'Use AJAX submissions?', 'advanced-form' ),
			'instructions' => 'Enables form submissions without a page reload',
			'name' => 'af_block_ajax',
			'type' => 'true_false',
			'ui' => 1,
			'wrapper' => array (
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_submit_text',
			'label' => __( 'Submit button text', 'advanced-forms' ),
			'name' => 'af_block_submit_text',
			'type' => 'text',
			'placeholder' => 'Submit',
			'wrapper' => array (
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_after_submission',
			'label' => __( 'After submission', 'advanced-forms' ),
			'name' => 'af_block_after_submission',
			'type' => 'radio',
			'choices' => array(
				'success_message' => 'Show success message',
				'redirect_link' => 'Redirect to another page',
				'redirect_url' => 'Redirect to a custom URL',
			),
			'wrapper' => array(
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_success_message_info',
			'label' => __( 'Success message', 'advanced-forms' ),
			'name' => 'af_block_success_message_info',
			'type' => 'message',
			'message' => __( 'You can configure the success message in your <a class="edit-form-link">form settings</a>', 'advanced-forms' ),
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_af_block_after_submission',
						'operator' => '==',
						'value' => 'success_message',
					),
				),
			),
			'wrapper' => array(
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_redirect_to_link',
			'label' => __( 'Redirect to', 'advanced-forms' ),
			'name' => 'af_block_redirect_to_link',
			'type' => 'page_link',
			'required' => true,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_af_block_after_submission',
						'operator' => '==',
						'value' => 'redirect_link',
					),
				),
			),
			'wrapper' => array(
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_redirect_to_url',
			'label' => __( 'Redirect to', 'advanced-forms' ),
			'name' => 'af_block_redirect_to_url',
			'type' => 'url',
			'required' => true,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_af_block_after_submission',
						'operator' => '==',
						'value' => 'redirect_url',
					),
				),
			),
			'wrapper' => array(
				'width' => '50',
			),
		);

		$fields[] = array(
			'key' => 'field_af_block_exclude_fields',
			'label' => __( 'Exclude fields', 'advanced-forms' ),
			'name' => 'af_block_exclude_fields',
			'type' => 'select',
			'required' => false,
			'choices' => array(),
			'multiple' => 1,
			'ui' => 1,
			'ajax' => 1,
			'placeholder' => __( 'Select fields to exclude from the form', 'advanced-forms' ),
		);

		return $fields;
	}

	function populate_form_select_field( $field ) {
		$choices = array();
		foreach ( af_get_forms() as $form ) {
			$field['choices'][ $form['key'] ] = $form['title'];
		}

		return $field;
	}

	function populate_exclude_fields_field( $field ) {
		if ( ! isset( $_POST['form'] ) ) {
			return $field;
		}

		$form_key = $_POST['form'];
		$fields = af_get_form_fields( $form_key );

		$choices = array();
		foreach ( $fields as $field ) {
			$choices[ $field['key'] ] = $field['label'];
		}
		$field['choices'] = $choices;

		return $field;
	}

	function ajax_get_form_data() {
		if ( ! isset( $_POST['form_key'] ) ) {
      		wp_send_json_error( array(
				'error' => 'Missing form key',
      		), 400 );
      		wp_die();
		}

		$form_key = $_POST['form_key'];
		$form = af_get_form( $form_key );
		$fields = af_get_form_fields( $form_key );

		$edit_link = $form['post_id'] ? get_edit_post_link( $form['post_id'], '&' ) : NULL;

		wp_send_json_success(array(
			'form' => $form,
			'fields' => $fields,
			'edit_url' => $edit_link,
		));
		wp_die();
	}
}

new AF_Core_Gutenberg();