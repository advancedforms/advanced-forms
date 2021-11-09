<?php
	

/**
 * Searches input for tags {field:FIELD_NAME} and replaces with field values.
 * Also replaces general tags such as {all_fields}.
 *
 * @since 1.0.1
 *
 */
function af_resolve_merge_tags( $input, $fields = false ) {
	// Get fields from the global submission object if fields weren't passed
	if ( ! $fields && af_has_submission() ) {
		$fields = AF()->submission['fields'];
	}

	// Find all merge tags
	if ( preg_match_all( "/{(.*?)}/", $input, $matches ) ) {
		foreach ( $matches[1] as $i=>$tag ) {
			// Resolve each merge tag and insert the value
			$value = apply_filters( 'af/merge_tags/resolve', '', $tag, $fields );
			$input = str_replace( $matches[0][ $i ], $value, $input );
		}
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
	} elseif ( 'flexible_content' === $field['type'] ) {
		$output .= '<table class="af-field-include af-field-include-flexible_content">';

		foreach ( $value as $row ) {
			$row_layout_name = $row['acf_fc_layout'];

			// Find layout based on name
			$row_layout = NULL;
			foreach ( $field['layouts'] as $layout ) {
				if ( $layout['name'] === $row_layout_name ) {
					$row_layout = $layout;
					break;
				}
			}

			// Output header with layout name for the row
			$output .= sprintf( '<tr><th>%s</th></tr>', $layout['label'] );
			$output .= '<tr><td>';

			// The subfield values will be displayed in a nested table, similar to a group field
			$output .= '<table class="af-field-include af-field-include-flexible_content-inner">';
			foreach ( $layout['sub_fields'] as $sub_field ) {
				if ( isset( $row[ $sub_field['name'] ] ) ) {
					$output .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
					$output .= sprintf( '<tr><td>%s</td></tr>', _af_render_field_include( $sub_field, $row[ $sub_field['name'] ] ) );
				}
			}
			$output .= '</table>';

			$output .= '</td></tr>';
		}

		$output .= '</table>';
	} elseif ( 'clone' == $field['type'] || 'group' == $field['type'] ) {
		$output .= sprintf( '<table class="af-field-include af-field-include-%s">', $field['type'] );
	
		foreach ( $field['sub_fields'] as $sub_field ) {
			if ( isset( $value[ $sub_field['name'] ] ) ) {
				$output .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
				$output .= sprintf( '<tr><td>%s</td></tr>', _af_render_field_include( $sub_field, $value[ $sub_field['name'] ] ) );
			}
		}
		
		$output .= '</table>';
	} elseif ( 'true_false' == $field['type'] ) {
		$true_text = isset( $field['ui_on_text'] ) && ! empty( $field['ui_on_text'] ) ? $field['ui_on_text'] : __( 'Yes', 'advanced-forms' );
		$false_text = isset( $field['ui_off_text'] ) && ! empty( $field['ui_off_text'] ) ? $field['ui_off_text'] : __( 'No', 'advanced-forms' );
		
		$output = $value ? $true_text : $false_text;
	} elseif ( 'image' == $field['type'] ) {
		$output .= sprintf( '<img src="%s" alt="%s" />', esc_attr( $value['sizes']['medium'] ), esc_attr( $value['alt']));
	} elseif ( 'gallery' == $field['type'] && is_array( $value ) ) {
		foreach ( $value as $image ) {
			$output .= sprintf( '<img src="%s" alt="%s" />', esc_attr( $image['sizes']['medium'] ), esc_attr( $image['alt']));
		}
	} elseif ( 'file' == $field['type'] ) {
		$output .= sprintf( '<a href="%s">%s</a>', $value['url'], htmlspecialchars( $value['title'] ) );
	} elseif ( in_array( $field['type'], array( 'wysiwyg', 'textarea', 'calculated' ) ) ) {
		// Sanitize input using kses
		$output .= wp_kses_post( stripslashes( $value ) );
	} else {
		$output = _af_render_field_include_value( $value ); 
	}
	
	// Allow third-parties to alter rendered field
	$output = apply_filters( 'af/field/render_include', $output, $field, $value );
	$output = apply_filters( 'af/field/render_include/name=' . $field['name'], $output, $field, $value );
	$output = apply_filters( 'af/field/render_include/key=' . $field['key'], $output, $field, $value );
	
	return $output;
}


/**
 * Handle the different shapes field values may take and create an appropriate string
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
function _af_render_field_include_value( $value ) {
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
			
			$rendered_values[] = _af_render_field_include_value( $single_value );
			
		}
		
		$rendered_value = join( ', ', $rendered_values );
		
	} else {
		
		$rendered_value = (string)$value;
		
	}

	// Sanitize output to protect against XSS
	return htmlspecialchars( $rendered_value );
}


/**
 * Output an "Insert field" button populated with $fields
 * $floating adds class "floating" to the wrapper making the button float right in an input field
 *
 * @since 1.1.1
 *
 */
function _af_field_inserter_button( $form, $type = 'all', $floating = false ) {	
	$classses = ( $floating ) ? 'floating' : '';
	
	echo '<a class="af-field-dropdown ' . $classses . ' button">' . __( 'Insert field', 'advanced-forms' );
		
	echo '<div class="af-dropdown">';

	$custom_tags = apply_filters( 'af/merge_tags/custom', array(), $form );
	foreach ( $custom_tags as $custom_tag ) {
		echo sprintf( '<div class="field-option" data-insert-value="{%s}">%s</div>', $custom_tag['value'], $custom_tag['label'] );
	}

	if ( ! empty( $custom_tags ) ) {
		echo '<div class="field-divider"></div>';
	}
	
	if ( 'all' == $type ) {
		echo sprintf( '<div class="field-option" data-insert-value="{all_fields}">%s</div>', __( 'All fields', 'advanced-forms' ) );
	}
	
	$fields = af_get_form_fields( $form, $type );
	foreach ( $fields as $field ) {
		_af_field_inserter_render_option( $field );
	}
	
	echo '</div>';
	echo '</a>';
}

function _af_field_inserter_render_option( $field, $ancestors = array() ) {
	$insert_value = '';
	if ( empty( $ancestors ) ) {
		$insert_value = sprintf( '{field:%s}', $field['name'] );
	} else {
		$hierarchy = array_merge( $ancestors, array( $field['name'] ) );
		$top_level_name = array_shift( $hierarchy );
		$insert_value = sprintf( '{field:%s[%s]}', $top_level_name, join( '][', $hierarchy ) );
	}
	
	$label = wp_strip_all_tags( $field['label'] );
	$type = acf_get_field_type_label( $field['type'] );

	echo sprintf( '<div class="field-option" data-insert-value="%s" role="button">', $insert_value );
	echo sprintf( '<span class="field-name">%s</span><span class="field-type">%s</span>', $label, $type );
	echo '</div>';

	// Append options for sub fields if they exist (and we are dealing with a group or clone field)
	$parent_field_types = array( 'group', 'clone' );
	if ( in_array( $field['type'], $parent_field_types ) && isset( $field['sub_fields'] ) ) {
		array_push( $ancestors, $field['name'] );

		echo '<div class="sub-fields-wrapper">';
		foreach ( $field['sub_fields'] as $sub_field ) {
			_af_field_inserter_render_option( $sub_field, $ancestors );
		}
		echo '</div>';
	}
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
 * Find a nested sub field based on some selector.
 * If the selector is ["g1", "g2", "f1"] then the function will find a
 * field named "f1" inside "g2" which is inside field "g1".
 * The $field parameter should be the top-level field, "g1" in the example.
 * 
 * @since 1.7.2
 * 
 */
function af_pick_sub_field( $field, $selector ) {
	while ( ! empty( $selector ) && $field && isset( $field['sub_fields'] ) ) {
		$search = array_shift( $selector );
		$field = acf_search_fields( $search, $field['sub_fields'] );
	}

	return $field;
}


/**
 * Find a nested value of a sub field based on some selector.
 * If the selector is ["g1", "g2", "f1"] then the function will return the
 * value of field "f1" inside "g2" which is inside "g1".
 * The $field parameter should be the top-level field, "g1" in the example.
 * 
 * @since 1.7.2
 * 
 */
function af_pick_sub_field_value( $field, $selector ) {
	$value = $field['value'];

	while ( ! empty( $selector ) ) {
		$search = array_shift( $selector );
		if ( isset( $value[ $search ] ) ) {
			$value = $value[ $search ];
		} else {
			return false;
		}
	}

	return $value;
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
