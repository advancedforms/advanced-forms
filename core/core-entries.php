<?php
	

/**
 * Handles the creation of entries
 *
 * @since 1.0.0
 *
 */
class AF_Core_Entries {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'create_entry' ), 10, 2 );
		
	}
	
	
	/**
	 * Create entry when form is submitted
	 *
	 * @since 1.0.0
	 *
	 */
	function create_entry( $form, $fields ) {
		
		// Make sure entries should be created
		if ( ! $form['create_entries'] ) {
			return;
		}
		
		
		// Create entry post
		$post_data = array(
			'post_type' 		=> 'af_entry',
			'post_status' 		=> 'publish',
			'post_title'		=> '',
		);
		
		$entry_id = wp_insert_post( $post_data );
		
		if ( ! $entry_id ) {
			return;
		}
		
		
		// Update post title
		$updated_title_data = array(
			'ID' 			=> $entry_id,
			'post_title' 	=> sprintf( '#%s', $entry_id ),
		);
		
		wp_update_post( $updated_title_data );
		
		
		// Save general entry info
		update_post_meta( $entry_id, 'entry_form', $form['key'] );
		update_post_meta( $entry_id, 'entry_submission_date', date( 'Y-m-d H:i:s' ) );
		update_post_meta( $entry_id, 'entry_ip_address', $_SERVER['REMOTE_ADDR'] );
		
		
		// Transfer all fields to the entry
		foreach ( $fields as $field ) {
			
			update_field( $field['key'], $field['_input'], $entry_id );
			
		}
	}
	
}

new AF_Core_Entries();