<?php
	
/**
 * 
 * @since 1.0.3
 */
class AF_Core_Restrictions {
	
	function __construct() {
		
		add_filter( 'af/form/restriction', array( $this, 'restrict_entries' ), 10, 3 );
		add_filter( 'af/form/restriction', array( $this, 'restrict_user_logged_in' ), 10, 3 );
		add_filter( 'af/form/restriction', array( $this, 'restrict_form_schedule' ), 10, 3 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		add_action( 'af/form/to_post', array( $this, 'form_to_post' ), 10, 2 );
		
	}
	
	
	/**
	 * Check if form should be restricted based on number of entries
	 *
	 * @since 1.0.3
	 */
	function restrict_entries( $restriction, $form, $args ) {
		
		if ( $restriction ) {
			return $restriction;
		}
		
		if ( $form['restrictions']['entries'] ) {
			
			if ( $form['restrictions']['entries']['max_entries'] <= af_get_entry_count( $form['key'] ) ) {
				
				return $form['restrictions']['entries']['message'];
				
			}
			
		}
		
		
		return false;
		
	}
	
	
	/**
	 * Check if form should be restricted based on user logged in
	 *
	 * @since 1.0.3
	 */
	function restrict_user_logged_in( $restriction, $form, $args ) {
		
		if ( $restriction ) {
			return $restriction;
		}
		
		
		if ( $form['restrictions']['user'] ) {
		
			if ( ! is_user_logged_in() ) {
				
				return $form['restrictions']['user']['message'];
				
			}
			
		}
		
		
		return false;
		
	}
	
	
	/**
	 * Check if form should be restricted based on a schedule
	 *
	 * @since 1.0.3
	 */
	function restrict_form_schedule( $restriction, $form, $args ) {
		
		if ( $restriction ) {
			return $restriction;
		}
		
		
		if ( $form['restrictions']['schedule'] ) {
			
			$current_time = current_time( 'timestamp' );
			$start_time = strtotime( $form['restrictions']['schedule']['start'] );
			$end_time = strtotime( $form['restrictions']['schedule']['end'] );
		
			// Before schedule
			if ( $current_time < $start_time ) {
				
				return $form['restrictions']['schedule']['message_before'];
				
			}
			
			// After schedule
			if ( $current_time > $end_time ) {
				
				return $form['restrictions']['schedule']['message_after'];
				
			}
			
		}
		
		
		return false;
		
	}
	
	
	/**
	 * Add the restriction fields to the default valid form
	 *
	 * @since 1.0.3
	 *
	 */
	function valid_form( $form ) {
		
		$form['restrictions'] = array(
			'entries' => false,
			'user' => false,
			'schedule' => false,
		);
		
		return $form;
		
	}
	
	
	/**
	 * Add any restriction settings to form object for forms loaded from posts
	 *
	 * @since 1.0.3
	 *
	 */
	function form_from_post( $form, $post ) {
		
		// Entries limit
		$restrict_entries = get_field( 'field_form_restrict_entries', $post->ID );
	
		if ( $restrict_entries ) {
			
			$form['restrictions']['entries'] = array(
				'max_entries' 	=> get_field( 'field_form_max_entries', $post->ID ),
				'message' 		=> get_field( 'form_entry_restriction_message', $post->ID ),
			);
			
		}
		
		
		// User logged in
		$require_login = get_field( 'field_form_require_login', $post->ID );
	
		if ( $require_login ) {
			
			$form['restrictions']['user'] = array(
				'message' => get_field( 'form_login_restriction_message', $post->ID ),
			);
			
		}
		
		
		// Scheduled form
		$schedule_form = get_field( 'field_form_schedule_form', $post->ID );
	
		if ( $schedule_form ) {
			
			$form['restrictions']['schedule'] = array(
				'start' 			=> get_field( 'field_form_schedule_start', $post->ID ),
				'end' 				=> get_field( 'field_form_schedule_end', $post->ID ),
				'message_before' 	=> get_field( 'field_form_before_schedule_message', $post->ID ),
				'message_after' 	=> get_field( 'field_form_after_schedule_message', $post->ID ),
			);
			
		}
		
		return $form;
		
	}


	/**
	 * Add restriction settings from form array to form post.
	 *
	 * @since 1.7.0
	 *
	 */
	function form_to_post( $form, $post ) {
		if ( $entries = $form['restrictions']['entries'] ) {
			update_field( 'field_form_restrict_entries', true, $post->ID );

			update_field( 'field_form_max_entries', $entries['max_entries'], $post->ID );
			update_field( 'field_form_entry_restriction_message', $entries['message'], $post->ID );
		}

		if ( $login = $form['restrictions']['user'] ) {
			update_field( 'field_form_require_login', true, $post->ID );
			
			update_field( 'field_form_login_restriction_message', $login['message'], $post->ID );
		}

		if ( $schedule = $form['restrictions']['schedule'] ) {
			update_field( 'field_form_schedule_form', true, $post->ID );

			update_field( 'field_form_schedule_start', $schedule['start'], $post->ID );
			update_field( 'field_form_schedule_end', $schedule['end'], $post->ID );
			update_field( 'field_form_before_schedule_message', $schedule['message_before'], $post->ID );
			update_field( 'field_form_after_schedule_message', $schedule['message_after'], $post->ID );
		}
	}
	
}

return new AF_Core_Restrictions();

