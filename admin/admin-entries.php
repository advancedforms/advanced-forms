<?php
	
	
class AF_Admin_Entries {
	
	function __construct() {
		
		// Actions
		add_action( 'init', array( $this, 'register_custom_fields' ), 10, 0 );
		
		add_action( 'manage_af_entry_posts_custom_column', array( $this, 'custom_columns_content' ), 10, 2 );
		
		add_action( 'restrict_manage_posts', array( $this, 'form_filter' ), 10, 0 );
		add_action( 'pre_get_posts', array( $this, 'filter_entries_by_form' ), 10, 1 );
		
		
		// Filters
		add_filter( 'acf/load_field/name=entry_form', array( $this, 'entry_form_field' ), 10, 1 );
		add_filter( 'acf/load_field/name=entry_submission_info', array( $this, 'entry_submission_info_field' ), 10, 1 );
		
		add_filter( 'manage_af_entry_posts_columns', array( $this, 'add_custom_columns' ), 10, 1 );
		
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
			
			$form = af_get_form( get_post_meta( $post->ID, 'entry_form', true ) );
			
			if ( $form ) {
				
				$field['instructions'] = sprintf( '<strong><a href="%s">%s</a></strong><br>%s', get_edit_post_link( $form['post_id'] ), $form['title'], $form['key'] );
				
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
			
			$time = strtotime( get_post_meta( $post->ID, 'entry_submission_date', true ) );
		
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			
			$field['instructions'] = '<strong>ID: </strong>#' . $post->ID;
			$field['instructions'] .= '<br>';
			$field['instructions'] .= '<strong>Date: </strong>' . date( $date_format . ' ' . $time_format, $time );
			$field['instructions'] .= '<br>';
			$field['instructions'] .= '<strong>IP Address: </strong>' . get_post_meta( $post->ID, 'entry_ip_address', true );
			
		}
		
		return $field;
		
	}
	
	
	/**
	 * Adds custom columns to the listings page
	 *
	 * @since 1.0.0
	 *
	 */
	function add_custom_columns( $columns ) {
		
		$new_columns = array(
			'form' 	=> 'Form',
		);
		
		return array_merge( array_splice( $columns, 0, 2 ), $new_columns, $columns );
		
	}
	
	
	/**
	 * Outputs the content for the custom columns
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
	 * Adds a drop down to filter by form
	 *
	 * @since 1.0.0
	 *
	 */
	function form_filter() {
		
		if ( 'af_entry' != $_GET['post_type'] ) {
			return;
		}
		
		$forms = af_get_forms();
		
		$current_form = '';
		if ( isset( $_GET['entry_form'] ) ) {
			$current_form = $_GET['entry_form'];
		}
		
		?>
		
		<select name="entry_form">
			<option value="">All forms</option>
			
			<?php
			foreach ( $forms as $form ) {
				
				$selected = ( $form['post_id'] == $current_form ) ? 'selected' : '';
				echo sprintf( '<option value="%s" %s>%s</option>', $form['post_id'], $selected, $form['title'] );
				
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
	 * Registers the ACF fields for the general entry data
	 *
	 * @since 1.0.0
	 *
	 */
	function register_custom_fields() {
		
		acf_add_local_field_group(array (
			'key' => 'group_entry_data',
			'title' => 'Entry data',
			'fields' => array (
				array (
					'key' => 'field_entry_form',
					'label' => 'Form',
					'name' => 'entry_form',
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
					'key' => 'field_entry_submission_info',
					'label' => 'Info',
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
					'key' => 'field_submission_info',
					'label' => 'Submission data',
					'name' => '',
					'type' => 'message',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => 'Below you will find all the data submitted through the form.',
					'new_lines' => 'wpautop',
					'esc_html' => 0,
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

new AF_Admin_Entries();