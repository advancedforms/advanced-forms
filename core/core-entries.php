<?php
/**
 * Handles the creation of entries
 *
 * @since 1.0.0
 *
 */
class AF_Core_Entries {
	function __construct() {
		add_action( 'af/form/submission', array( $this, 'create_entry' ), 1, 3 );
		add_action( 'save_post', array( $this, 'entry_saved' ), 10, 3 );

		add_action( 'af/merge_tags/custom', array( $this, 'add_entry_id_tag' ), 10, 2 );
		add_action( 'af/merge_tags/resolve', array( $this, 'resolve_entry_id_tag' ), 10, 2 );
	}
	
	/**
	 * Create entry when form is submitted
	 *
	 * @since 1.0.0
	 *
	 */
	function create_entry( $form, $fields, $args ) {
		// Make sure entries should be created
		if ( ! $form['create_entries'] ) {
			return;
		}

		// Enable users to stop entry creation using a filter
		$should_create = true;
		$should_create = apply_filters( 'af/form/entry/should_create', $should_create, $form, $args );
		$should_create = apply_filters( 'af/form/entry/should_create/post=' . $form['post_id'], $should_create, $form, $args );
		$should_create = apply_filters( 'af/form/entry/should_create/key=' . $form['key'], $should_create, $form, $args );
		if ( ! $should_create ) {
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
		
		// Transfer all fields to the entry
		af_save_all_fields( $entry_id );
		
		// Save generated entry ID to submission object
		AF()->submission['entry'] = $entry_id;

		// Trigger entry
		do_action( 'af/form/entry_created', $entry_id, $form );
		do_action( 'af/form/entry_created/id=' . $form['post_id'], $entry_id, $form );
		do_action( 'af/form/entry_created/key=' . $form['key'], $entry_id, $form );
	}
	
	/**
	 * Sets some basic entry post data, such as the post title matching the ID
	 *
	 * @since 1.0.1
	 *
	 */
	function entry_saved( $post_id, $post, $update ) {
		// Bail if we are not dealing with an entry
		if ( 'af_entry' != $post->post_type ) {
			return;
		}
		
		// Check if an post title has been set
		if ( ! $post->post_title || empty( $post->post_title ) ) {
			remove_action( 'save_post', array( $this, 'entry_saved' ) );
			
			// Set the post title to match the ID
			$updated_post_data = array(
				'ID' => $post_id,
				'post_title' => sprintf( '#%s', $post_id ),
			);
			
			wp_update_post( $updated_post_data );
			
			add_action( 'save_post', array( $this, 'entry_saved' ), 10, 3 );
		}
	}

	/**
	 * Add custom merge tag {entry_id}
	 *
	 * @since 1.6.0
	 *
	 */
	function add_entry_id_tag( $tags, $form ) {
		$tags[] = array(
			'value' => 'entry_id',
			'label' => __( 'Entry ID', 'advanced-forms' ),
		);

		return $tags;
	}

	/**
	 * Resolve merge tags on the form {entry_id}
	 *
	 * @since 1.6.0
	 *
	 */
	function resolve_entry_id_tag( $output, $tag ) {
		if ( ! empty( $output ) || 'entry_id' != $tag ) {
			return $output;
		}

		$entry_id = isset( AF()->submission['entry'] ) ? AF()->submission['entry'] : '';
		return $entry_id;
	}
}

return new AF_Core_Entries();