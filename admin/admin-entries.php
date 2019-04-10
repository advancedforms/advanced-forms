<?php
	
	
class AF_Admin_Entries {
	
	function __construct() {
		
		// Actions
		add_action( 'acf/init', array( $this, 'register_custom_fields' ), 10, 0 );
		
		add_action( 'manage_af_entry_posts_custom_column', array( $this, 'custom_columns_content' ), 10, 2 );
		
		add_action( 'restrict_manage_posts', array( $this, 'form_filter' ), 10, 0 );
		add_action( 'pre_get_posts', array( $this, 'filter_entries_by_form' ), 10, 1 );
		
		
		// Filters
		add_filter( 'acf/prepare_field/name=entry_form', array( $this, 'entry_form_field' ), 10, 1 );
		add_filter( 'acf/prepare_field/name=entry_submission_info', array( $this, 'entry_submission_info_field' ), 10, 1 );
		
		add_filter( 'acf/prepare_field/name=form_create_entries', array( $this, 'add_entries_link_to_instruction' ), 10, 1 );
		
		add_filter( 'manage_af_entry_posts_columns', array( $this, 'add_custom_columns' ), 10, 1 );
		
		add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 10, 1 );
		
	}	
	
	
	/**
	 * Change the display of the entry form field to show the form name
	 *
	 * @since 1.0.0
	 *
	 */
	function entry_form_field( $field ) {
		
		global $post;
		
		if ( $post && 'af_entry' == $post->post_type ) {
			
			// Populate the select with all forms
			$forms = af_get_forms();
			
			$field['choices'] = array();
			
			foreach ( $forms as $form ) {
				$field['choices'][ $form['key'] ] = sprintf( '%s (%s)', $form['title'], $form['key'] );
			}
			
			
			// Get the current form, should be false if we are creating a new entry
			$form = af_get_form( get_post_meta( $post->ID, 'entry_form', true ) );
			
			if ( $form ) {
				
				// Add a link to edit the form
				if ( $form['post_id'] ) {
					
					$field['instructions'] = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $form['post_id'] ), __( 'Edit form', 'advanced-forms' ) );
					
				}
				
				// Add the current form to the choices if it isn't already set. Could happen if a form is deleted
				if ( ! isset( $field['choices'][ $form['key'] ] ) ) {
					
					$field['choices'][ $form['key'] ] = sprintf( '%s (%s)', $form['title'], $form['key'] );
					
				}
				
			}
			
		}
		
		return $field;
		
	}
	
	
	/**
	 * Change the format of the submission date field to match the Wordpress settings
	 *
	 * @since 1.0.0
	 *
	 */
	function entry_submission_info_field( $field ) {
		
		global $post;
		
		if ( $post && 'af_entry' == $post->post_type ) {
			
			$time = get_post_meta( $post->ID, 'entry_submission_date', true );
		
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			
			$field['instructions'] = sprintf( '<strong>%s: </strong>#%d', __( 'Entry ID', 'advanced-forms' ), $post->ID );
			$field['instructions'] .= '<br>';
			$field['instructions'] .= sprintf( '<strong>%s: </strong>%s', __( 'Date', 'advanced-forms' ), get_date_from_gmt( $time, $date_format ) );
			$field['instructions'] .= '<br>';
			$field['instructions'] .= sprintf( '<strong>%s: </strong>%s', __( 'Time', 'advanced-forms' ), get_date_from_gmt( $time, $time_format ) ); 
			
		}
		
		return $field;
		
	}
	
	
	/**
	 * Add custom columns to listings page
	 *
	 * @since 1.0.0
	 *
	 */
	function add_custom_columns( $columns ) {
		
		$new_columns = array(
			'form' 	=> __( 'Form', 'advanced-forms' ),
		);
		
		return array_merge( array_splice( $columns, 0, 2 ), $new_columns, $columns );
		
	}
	
	
	/**
	 * Output content for custom columns
	 *
	 * @since 1.0.0
	 *
	 */
	function custom_columns_content( $column, $post_id ) {
		
		if ( 'form' == $column ) {
			
			$form_id = get_post_meta( $post_id, 'entry_form', true );
			$form = af_get_form( $form_id );
			
			echo sprintf( '<a href="%s">%s</a>', get_edit_post_link( $form['post_id'] ), $form['title'] );
			
		}
		
	}
	
	
	/**
	 * Add drop down to filter by form on listings page
	 *
	 * @since 1.0.0
	 *
	 */
	function form_filter() {
		
		if ( ! isset( $_GET['post_type'] ) || 'af_entry' != $_GET['post_type'] ) {
			return;
		}
		
		$forms = af_get_forms();
		
		$current_form = '';
		if ( isset( $_GET['entry_form'] ) ) {
			$current_form = $_GET['entry_form'];
		}
		
		?>
		
		<select name="entry_form">
			<option value=""><?php _e( 'All forms', 'advanced-forms' ); ?></option>
			
			<?php
			foreach ( $forms as $form ) {
				
				$selected = ( $form['key'] == $current_form ) ? 'selected' : '';
				echo sprintf( '<option value="%s" %s>%s</option>', $form['key'], $selected, $form['title'] );
				
			}
			?>
		</select>
		
		<?php
	}
	
	
	/**
	 * Filters by form if the dropdown has been set
	 *
	 * @since 1.0.0
	 *
	 */
	function filter_entries_by_form( $query ) {
	
		if ( is_admin() && isset( $_GET['entry_form'] ) && $_GET['entry_form'] != '' && 'af_entry' == $query->query['post_type'] ) {
			
			$query->set( 'meta_query', array(
				array(
					'key' => 'entry_form',
					'value'=> $_GET['entry_form'],
				),
			) );
			
		}
	
	}
	
	
	/**
	 * Adds a link to view entries for a form in the "Create entries?" form instructions
	 *
	 * @since 1.0.0
	 *
	 */
	function add_entries_link_to_instruction( $field ) {
		
		global $post;
		
		if ( $post && get_post_meta( $post->ID, 'form_create_entries', true) ) {
			
			$form = af_get_form( $post->ID );
			
			$field['instructions'] .= sprintf( ' <a href="%s">%s</a>', admin_url() . '/edit.php?post_type=af_entry&entry_form=' . $form['key'], __( 'View entries for this form', 'advanced-forms' ) );
			
		}
		
		return $field;
		
	}
	
	
	/**
	 * Add form settings for entries
	 *
	 * @since 1.0.2
	 *
	 */
	function add_form_settings_fields( $field_group ) {
		
		$field_group['fields'][] = array(
			'key' => 'field_form_entries_tab',
			'label' => '<span class="dashicons dashicons-editor-table"></span>' . __( 'Entries', 'advanced-forms' ),
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
		
		
		$field_group['fields'][] = array(
			'key' => 'field_form_create_entries',
			'label' => __( 'Create entries?', 'advanced-forms' ),
			'name' => 'form_create_entries',
			'type' => 'true_false',
			'instructions' => __( 'When entries are enabled they will be automatically generated with form submissions, with all the submitted field data.', 'advanced-forms' ),
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => 'Yes',
			'ui_off_text' => 'No',
		);
				
		
		return $field_group;
		
	}
	
	
	/**
	 * Register ACF fields for general entry data
	 *
	 * @since 1.0.0
	 *
	 */
	function register_custom_fields() {
		
		acf_add_local_field_group(array (
			'key' => 'group_entry_data',
			'title' => __( 'Entry data', 'advanced-forms' ),
			'fields' => array (
				array (
					'key' => 'field_entry_submission_info',
					'label' => __( 'Submission info', 'advanced-forms' ),
					'name' => 'entry_submission_info',
					'type' => 'message',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
						'class' => '',
						'id' => '',
					),
				),
				array (
					'key' => 'field_entry_form',
					'label' => __( 'Form', 'advanced-forms' ),
					'name' => 'entry_form',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
						'class' => '',
						'id' => '',
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'af_entry',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));
		
	}
	
}

return new AF_Admin_Entries();