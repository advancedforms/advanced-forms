<?php
	

/**
 * Searches input for tags {field:FIELD_NAME} and replaces with field values.
 * Also replaces general tags such as {all_fields}.
 *
 * @since 1.0.1
 *
 */
function af_resolve_field_includes( $input, $fields = false ) {
	
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		
		$fields = AF()->submission['fields'];
		
	}
	
	
	// Render all fields as a table e.g. {all_fields}
	if ( preg_match_all( "/{all_fields}/", $input, $matches ) ) {
		
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
		
		
		$input = str_replace( '{all_fields}', $output, $input );
		
	}


	// Render sub fields individually e.g. {field:group_field[sub_field]}
	if ( preg_match_all( "/{field:(.*?)\[(.*?)\]}/", $input, $matches ) ) {
		
		foreach ( $matches[1] as $i => $field_name ) {
			
			$field = af_get_field_object( $field_name );

			$sub_field_name = $matches[2][ $i ];


			if ( isset( $field['sub_fields'] ) ) {

				foreach ( $field['sub_fields'] as $sub_field ) {

					if ( $sub_field['name'] == $sub_field_name || $sub_field['key'] == $sub_field_name ) {

						$rendered_value = _af_render_field_include( $sub_field, $field['value'][ $sub_field['name'] ] );

						$input = str_replace( $matches[0][$i], $rendered_value, $input );

					}

				}

			}

		}
		
	}
	
	
	// Render single fields individually e.g. {field:field_name}
	if ( preg_match_all( "/{field:(.*?)}/", $input, $matches ) ) {
		
		foreach ($matches[1] as $i => $field_name ) {
			
			$field = af_get_field_object( $field_name );
			
			$rendered_value = _af_render_field_include( $field );
			
			$input = str_replace( $matches[0][$i], $rendered_value, $input );
			
		}
		
	}


	// Render entry ID e.g. {entry_id}
	if ( preg_match_all( "/{entry_id}/", $input, $matches ) ) {

		$entry_id = isset( AF()->submission['entry'] ) ? AF()->submission['entry'] : '';
		
		$input = str_replace( '{entry_id}', $entry_id, $input );
		
	}
	
	return $input;
	
}


/**
 * Renders a single field include (for emails, success messages etc.)
 *
 * @since 1.2.0
 *
 */
function _af_render_field_include( $field, $value = false ) {
	
	if ( ! $value ) {
		$value = $field['value'];
	}
	

	$output = '';
	
	if ( 'repeater' == $field['type'] && is_array( $value ) ) {
		
		$output .= '<table class="af-field-include af-field-include-repeater">';
		
		// Column headings
		$output .= '<thead><tr>';
		
		foreach ( $field['sub_fields'] as $sub_field ) {
			$output .= sprintf( '<th>%s</th>', $sub_field['label'] );
		}
		
		$output .= '</tr></thead>';
		
		
		// Rows
		$output .= '<tbody>';
		
		if ( is_array( $value ) ) {
			foreach ( $value as $row_values ) {
				$output .= '<tr>';
				
				foreach ( $field['sub_fields'] as $sub_field ) {
					
					$output .= sprintf( '<td>%s</td>', _af_render_field_include( $sub_field, $row_values[ $sub_field['name'] ] ) );
					
				}
				
				$output .= '</tr>';
			}
		}
		
		$output .= '</tbody>';
		
		
		$output .= '</table>';
		
	} elseif ( 'clone' == $field['type'] || 'group' == $field['type'] ) {
		
		$output .= sprintf( '<table class="af-field-include af-field-include-%s">', $field['type'] );
	
		foreach ( $field['sub_fields'] as $sub_field ) {
			
			$output .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
			$output .= sprintf( '<tr><td>%s</td></tr>', _af_render_field_include( $sub_field, $value[ $sub_field['name'] ] ) );
			
		}
		
		$output .= '</table>';
		
	} elseif ( 'true_false' == $field['type'] ) {
	
		$true_text = isset( $field['ui_on_text'] ) && ! empty( $field['ui_on_text'] ) ? $field['ui_on_text'] : __( 'Yes', 'advanced-forms' );
		$false_text = isset( $field['ui_off_text'] ) && ! empty( $field['ui_off_text'] ) ? $field['ui_off_text'] : __( 'No', 'advanced-forms' );
		
		$output = $value ? $true_text : $false_text;
	
	} elseif ( 'image' == $field['type'] ) {

		$output .= sprintf( '<img src="%s" alt="%s" />', esc_attr( $value['sizes']['medium'] ), esc_attr( $value['alt']));

	} elseif ( 'file' == $field['type'] ) {

		$output .= sprintf( '<a href="%s">%s</a>', $value['url'], htmlspecialchars( $value['title'] ) );

	} elseif ( 'wysiwyg' == $field['type'] ) {

		// Output WYSIWYG content without sanitation
		$output .= stripslashes( $value );

	} else {
		
		/**
		 * Handle the different shapes $value may take and create an appropriate string
		 *
		 * WP_Post 		- post title
		 * WP_User 		- user first name and last name combined
		 * User array	- user first name and last name combined
		 * WP_Term 		- term name
		 * Array 		- render each value and join with commas
		 * Other 		- cast to string
		 *
		 * @since 1.3.0
		 *
		 */
		$rendered_value = '';
		 
		if ( $value instanceof WP_Post ) {
			
			$rendered_value = $value->post_title;
			
		} elseif ( $value instanceof WP_User ) {
			
			$rendered_value = sprintf( '%s %s', $value->first_name, $value->last_name );
		
		} elseif ( is_array( $value ) && isset( $value['user_email'] ) ) {
			
			$rendered_value = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
			
		} elseif ( $value instanceof WP_Term ) {
			
			$rendered_value = $value->name;
			
		} elseif ( is_array( $value ) ) {
			
			$rendered_values = array();
			
			foreach ( $value as $single_value ) {
				
				$rendered_values[] = _af_render_field_include( $field, $single_value );
				
			}
			
			$rendered_value = join( ', ', $rendered_values );
			
		} else {
			
			$rendered_value = (string)$value;
			
		}

		// Sanitize output to protect against XSS
		$output = htmlspecialchars( $rendered_value );
		
	}
	
	// Allow third-parties to alter rendered field
	$output = apply_filters( 'af/field/render_include', $output, $field, $value );
	$output = apply_filters( 'af/field/render_include/name=' . $field['name'], $output, $field, $value );
	$output = apply_filters( 'af/field/render_include/key=' . $field['key'], $output, $field, $value );
	
	return $output;
	
}


/**
 * Output an "Insert field" button populated with $fields
 * $floating adds class "floating" to the wrapper making the button float right in an input field
 *
 * @since 1.1.1
 *
 */
function _af_field_inserter_button( $form, $type = 'all', $floating = false ) {
	
	$fields = af_get_form_fields( $form, $type );
	
	
	$classses = ( $floating ) ? 'floating' : '';
	
	echo '<a class="af-field-dropdown ' . $classses . ' button">' . __( 'Insert field', 'advanced-forms' );
		
	echo '<div class="af-dropdown">';

	echo sprintf( '<div class="field-option" data-insert-value="{entry_id}">%s</div>', __( 'Entry ID', 'advanced-forms' ) );
		echo '<div class="field-divider"></div>';
	
	if ( 'all' == $type ) {
		
		echo sprintf( '<div class="field-option" data-insert-value="{all_fields}">%s</div>', __( 'All fields', 'advanced-forms' ) );
		
	}
	
	foreach ( $fields as $field ) {
		
		echo sprintf( '<div class="field-option" data-insert-value="{field:%s}">', $field['name'] );
		echo sprintf( '<span class="field-name">%s</span><span class="field-type">%s</span>', $field['label'], acf_get_field_type_label( $field['type'] ) );
		echo '</div>';

		// Append options for sub fields if they exist (and we are dealing with a group or clone field)
		$parent_field_types = array( 'group', 'clone' );
		if ( in_array( $field['type'], $parent_field_types ) && isset( $field['sub_fields'] ) ) {

			foreach ( $field['sub_fields'] as $sub_field ) {

				echo sprintf( '<div class="field-option sub-field" data-insert-value="{field:%s[%s]}">', $field['name'], $sub_field['name'] );
				echo sprintf( '<span class="field-name">%s</span><span class="field-type">%s</span>', $sub_field['label'], acf_get_field_type_label( $sub_field['type'] ) );
				echo '</div>';

			}

		}
		
	}
	
	echo '</div>';
		
	echo '</a>';
	
}


/**
 * Generates choices for a form field picker.
 * Returns an array with field key => field label suitable for usage with an ACF select field.
 *
 * $type can be either 'all' or 'regular'.
 *
 * @since 1.3.0
 *
 */
function _af_form_field_choices( $form_key, $type = 'all' ) {
	
	$form_fields = af_get_form_fields( $form_key, $type );
	
	$choices = array();
	
	if ( ! empty( $form_fields ) ) {
		
		foreach ( $form_fields as $field ) {
				
			$choices[ $field['key'] ] = $field['label'];
			
		}
		
	}
	
	
	return $choices;
	
}


/**
 * Retrieves full URL (with trailing slash) to the plugin assets folder
 *
 * @since 1.3.0
 *
 */
function af_assets_url( $path = '' ) {
	
	return plugin_dir_url( dirname( __FILE__ ) ) . 'assets/' . $path;
	
}