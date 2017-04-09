<?php
	
	
/**
 * Handles rendering, validation, and saving of forms
 *
 * @since 1.0.0
 *
 */
class AF_Core_Forms {
	
	
	function __construct() {
		
		add_action( 'init', array( $this, 'pre_form' ), 10, 0 );
		add_action( 'af/form/render', array( $this, 'render' ), 10, 2 );
		
		add_shortcode( 'advanced_form', array( $this, 'form_shortcode' ) );
		
	}
	
	
	/**
	 * Registers the shortcode advanced_form which renders the form specified by the "form" attribute
	 *
	 * @since 1.0.0
	 *
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
	 * Handles submissions and enqueue of neccessary scripts
	 * Relies on default ACF validations
	 *
	 * @since 1.0.0
	 *
	 */
	function pre_form() {
		
		if ( isset( $_POST['af_form'] ) && ! isset( $_POST['action'] ) ) {
			
			$form_key_or_id = $_POST['af_form'];
			
			$form = af_get_form( $form_key_or_id );
			
			// Validate the posted data, this validation has already been performed once over AJAX
			if ( $form && acf_validate_save_post( true ) ) {
				
				
				// Increase the form submissions counter
				if ( $form['post_id'] ) {
					$submissions = get_post_meta( $form['post_id'], 'form_num_of_submissions', true );
					$submissions = $submissions ? $submissions + 1 : 1;
					update_post_meta( $form['post_id'], 'form_num_of_submissions', $submissions );
				}
				
				
				// Retrieve the args used to display the form
				$args = json_decode( base64_decode( $_POST['af_form_args'] ), true );
				
				$fields = array();
				
				
				$field_groups = af_get_form_field_groups( $form['key'] );
				
				foreach( $field_groups as $field_group ) {
					
					$group_fields = acf_get_fields( $field_group );
					
					foreach( $group_fields as $group_field ) {
						
						// Format value from POST data
						if ( isset( $_POST['acf'][ $group_field['key'] ] ) ) {
							
							$value = $_POST['acf'][ $group_field['key'] ];
							
							$group_field['_input'] = $value;
							$group_field['value'] = acf_format_value( $value, 0, $group_field );
							
						}
						
						$fields[] = $group_field;
						
					}
					
				}
				
				
				// Save submission data to the global AF object
				AF()->submission = array(
					'form' => $form,
					'args' => $args,
					'fields' => $fields,
				);
				
				
				do_action( 'af/form/submission', $form, $fields, $args );
				do_action( 'af/form/submission/id=' . $form['post_id'], $form, $fields, $args );
				do_action( 'af/form/submission/key=' . $form['key'], $form, $fields, $args );
				
				
				// Redirect to different URL if redirect argument has been passed
				if ( $args['redirect'] && '' != $args['redirect'] ) {
					
					wp_redirect( $args['redirect'] );
					
					exit;
					
				}
				
				
			}
			
		}
		
	}
	
	
	/**
	 * Renders the form specified by ID
	 *
	 * @since 1.0.0
	 *
	 */
	function render( $form_id_or_key, $args ) {
		
		$form = af_get_form( $form_id_or_key );
		
		if ( ! $form ) {
			return;
		}
		
		
		/**
		 * Enqueue ACF scripts and styles
		 *
		 * Normally ACF initializes the global JS object in wp_head but we only want to include the scripts when displaying a form.
		 * To work around this we enqueue using the regular ACF function and then immediately include the acf-input.js script.
		 * If acf-input.js is not initialized before the fields then conditional logic doesn't work. The remaining scripts/styles will be included in wp_footer.
		 *
		 * @since 1.1.1
		 *
		 */
		acf_enqueue_scripts();
		
		global $wp_scripts;
		
		if ( wp_script_is( 'acf-input', 'enqueued' ) ) {
			
			$wp_scripts->do_item( 'acf-input' );
			wp_dequeue_script( 'acf-input' );
			
		}
		
		if ( wp_script_is( 'acf-pro-input', 'enqueued' ) ) {
			
			$wp_scripts->do_item( 'acf-pro-input' );
			wp_dequeue_script( 'acf-pro-input' );
			
		}
		
		
		$args = wp_parse_args($args, array(
			'display_title' 			=> false,
			'display_description' 		=> false,
			'values' 					=> array(),
			'submit_text' 				=> __( 'Submit', 'advanced-forms' ),
			'redirect' 					=> false,
			'target'					=> acf_get_current_url(),
			'echo'						=> true,
			'exclude_fields'			=> array(),
			'uploader'					=> 'wp',
		));
		
		
		// Allow the arguments to be modified before rendering form
		$args = apply_filters( 'af/form/args', $args, $form );
		$args = apply_filters( 'af/form/args/id=' . $form['post_id'], $args, $form );
		$args = apply_filters( 'af/form/args/key=' . $form['key'], $args, $form );
		
		
		// Allow the form to be modified before rendering form
		$form = apply_filters( 'af/form/before_render', $form, $args );
		$form = apply_filters( 'af/form/before_render/id=' . $form['post_id'], $form, $args );
		$form = apply_filters( 'af/form/before_render/key=' . $form['key'], $form, $args );
		
		
		// Increase the form view counter
		if ( $form['post_id'] ) {
			$views = get_post_meta( $form['post_id'], 'form_num_of_views', true );
			$views = $views ? $views + 1 : 1;
			update_post_meta( $form['post_id'], 'form_num_of_views', $views );
		}
		
		
		// Form element
		echo sprintf( '<form class="af-form acf-form" method="POST" action="%s">', $args['target'] );
		
		
		do_action( 'af/form/before_title', $form, $args );
		do_action( 'af/form/before_title/id=' . $form['post_id'], $form, $args );
		do_action( 'af/form/before_title/key=' . $form['key'], $form, $args );
		
		
		// Display title
		if ( $args['display_title'] ) {
			
			echo sprintf( '<h1 class="af-title">%s</h1>', $form['title'] );
			
		}
		
		
		// Display description
		if ( $args['display_description'] ) {
			
			echo sprintf( '<div class="af-description">%s</div>', $form['display']['description'] );
			
		}
		
		
		/**
		 * Check if form should be restricted and not displayed.
		 * Filter will return false if no restriction is applied otherwise it will return a string to display.
		 */
		$restriction = false;
		$restriction = apply_filters( 'af/form/restriction', $restriction, $form, $args );
		$restriction = apply_filters( 'af/form/restriction/id=' . $form['post_id'], $restriction, $form, $args );
		$restriction = apply_filters( 'af/form/restriction/key=' . $form['key'], $restriction, $form, $args );
		
		
		// Display success message, restriction message, or fields
		if ( af_has_submission() ) {
			
			echo '<div class="af-success">';
			
			echo af_resolve_field_includes( $form['display']['success_message'] );
			
			echo '</div>';
			
		} elseif ( $restriction ) {
		
			echo '<div class="af-restricted-message">';
			
			echo $restriction;
			
			echo '</div>';
		
		} else {
			
			// Set ACF uploader type setting
			acf_update_setting( 'uploader', $args['uploader'] );
			
			
			// Get field groups for the form and display their fields
			$field_groups = af_get_form_field_groups( $form['key'] );
			
			
			echo '<div class="af-fields acf-fields acf-form-fields">';
			
			
			do_action( 'af/form/before_fields', $form, $args );
			do_action( 'af/form/before_fields/id=' . $form['post_id'], $form, $args );
			do_action( 'af/form/before_fields/key=' . $form['key'], $form, $args );
			
			
			// Dummy ACF data to make Javascript validations work
			echo '<div id="acf-form-data">';
			echo '<input type="hidden" name="_acfnonce" value="">';
			echo '</div>';
			
			
			// Hidden fields to identify form
			echo '<div class="acf-hidden">';
			echo sprintf( '<input type="hidden" name="af_form" value="%s">', $form['key'] );
			echo sprintf( '<input type="hidden" name="af_form_args" value="%s">', base64_encode( json_encode( $args ) ) );
			echo sprintf( '<input type="hidden" name="_acf_form" value="%s">', base64_encode( json_encode( $args ) ) );
			
			do_action( 'af/form/hidden_fields', $form, $args );
			do_action( 'af/form/hidden_fields/id=' . $form['post_id'], $form, $args );
			do_action( 'af/form/hidden_fields/key=' . $form['key'], $form, $args );
			
			echo '</div>';
			
			
			foreach ( $field_groups as $field_group ) {
				
				// Get all fields for field group
				$fields = acf_get_fields( $field_group );
				
				foreach ( $fields as $field ) {
					
					// Skip field if it is in the exluded fields argument
					if ( isset( $args['exclude_fields'] ) && is_array( $args['exclude_fields'] ) ) {
						
						if ( in_array( $field['key'], $args['exclude_fields'] ) || in_array( $field['name'], $args['exclude_fields'] ) ) {
							continue;
						}
						
					}
					

					// Check if we have any prefilled values for this field, either in the args or previously submitted
					if ( isset( $_POST['acf'][ $field['key'] ] ) ) {
					
						$field['value'] = $_POST['acf'][ $field['key'] ];
					
					} elseif ( isset( $args['values'][ $field['name'] ] ) ) {
						
						$field['value'] = $args['values'][ $field['name'] ];
						
					} elseif ( isset( $args['values'][ $field['key'] ] ) ) {
						
						$field['value'] = $args['values'][ $field['key'] ];
						
					}
					
					
					// Attributes to be used on the wrapper element
					$attributes = array();
					
					$attributes['id'] = $field['wrapper']['id'];
					
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
					
					
					$attributes = apply_filters( 'af/form/field_attributes', $attributes, $field, $form, $args );
					$attributes = apply_filters( 'af/form/field_attributes/id=' . $form['post_id'], $attributes, $field, $form, $args );
					$attributes = apply_filters( 'af/form/field_attributes/key=' . $form['key'], $attributes, $field, $form, $args );
					
					
					// Field wrapper
					echo sprintf( '<div %s>', acf_esc_attr( $attributes ) );
					
					
					echo '<div class="af-label acf-label">';
					echo sprintf( '<label>%s</label>', $field['label'] );
					echo '</div>';
					
					
					if ( '' != $field['instructions'] ) {
						echo sprintf( '<p class="af-field-instructions">%s</p>', $field['instructions'] );
					}
					
					
					echo '<div class="af-input acf-input">';
					
					// Render field with default ACF
					acf_render_field( $field );
					
					echo '</div>';
					
					
					// Conditional logic Javascript
					if ( ! empty( $field['conditional_logic'] ) ) {
						?>
						<script type="text/javascript">
							if(typeof acf !== 'undefined'){ acf.conditional_logic.add( '<?php echo $field['key']; ?>', <?php echo json_encode($field['conditional_logic']); ?>); }
						</script>
						<?php
					}
					
					
					// End field wrapper
					echo '</div>';
					
				}
				
			}
			
			
			// Submit button and loading indicator
			echo '<div class="af-submit acf-form-submit">';
				echo sprintf( '<input type="submit" class="acf-button af-submit-button" value="%s">', $args['submit_text'] );
				echo '<span class="acf-spinner af-spinner"></span>';
			echo '</div>';
			
			
			do_action( 'af/form/after_fields', $form, $args );
			do_action( 'af/form/after_fields/id=' . $form['post_id'], $form, $args );
			do_action( 'af/form/after_fields/key=' . $form['key'], $form, $args );
			
			// End fields wrapper
			echo '</div>';
		
		}
		
		// End form
		echo '</form>';
		
	}
	
	
}

new AF_Core_Forms();
