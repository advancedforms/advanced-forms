<?php
	
class AF_Core_Emails {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'send_form_emails' ), 10, 2 );
		add_action( 'af/emails/send_form_email', array( $this, 'send_single_form_email' ), 10, 3 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
	}
	
	
	/**
	 * Add the email field to the default valid form
	 *
	 * @since 1.0.0
	 *
	 */
	function valid_form( $form ) {
		
		$form['emails'] = false;
		
		return $form;
		
	}
	
	
	/**
	 * Add any email settings to form object for forms loaded from posts
	 *
	 * @since 1.0.0
	 *
	 */
	function form_from_post( $form, $post ) {
		
		$emails = get_field( 'field_form_emails', $post->ID );
	
		if ( $emails && ! empty( $emails ) ) {
	
			$form['emails'] = $emails;
			
		}
		
		return $form;
		
	}
	
	
	/**
	 * Send form emails after submission
	 *
	 * @since 1.0.0
	 *
	 */
	function send_form_emails( $form, $fields ) {
		
		$emails = $form['emails'];
		
		if ( $emails && ! empty( $emails ) ) {
			
			foreach ( $emails as $email ) {
				
				do_action( 'af/emails/send_form_email', $email, $form, $fields );
				
			}
			
		}
		
	}
	
	
	/**
	 * Send single form email
	 *
	 * @since 1.0.0
	 *
	 */
	function send_single_form_email( $email, $form, $fields ) {

		// Bail if this email is deactivated
		if ( ! $email['active'] ) {
			
			return;
			
		}
		
		
		// Recipient
		$recipient = '';
		
		if ( 'field' == $email['recipient_type'] ) {
			
			foreach( $fields as $field ) {
				
				if ( $field['key'] == $email['recipient_field'] ) {
					
					$recipient = $field['_input'];
					
				}
				
			}
			
		} elseif ( 'custom' == $email['recipient_type'] ) {
			
			$recipient = $email['recipient_custom'];
			
		}
		
		$recipient = apply_filters( 'af/form/email/recipient', $recipient, $email, $form, $fields );
		$recipient = apply_filters( 'af/form/email/recipient/id=' . $form['post_id'], $recipient, $email, $form, $fields );
		$recipient = apply_filters( 'af/form/email/recipient/key=' . $form['key'], $recipient, $email, $form, $fields );
		
		$recipient = af_resolve_field_includes( $recipient, $fields );
		
		
		// Subject line
		$subject = $email['subject'];
		$subject = apply_filters( 'af/form/email/subject', $subject, $email, $form, $fields );
		$subject = apply_filters( 'af/form/email/subject/id=' . $form['post_id'], $subject, $email, $form, $fields );
		$subject = apply_filters( 'af/form/email/subject/key=' . $form['key'], $subject, $email, $form, $fields );
		
		$subject = af_resolve_field_includes( $subject, $fields );
		
		
		// Email contents
		$content = $email['content'];
		$content = apply_filters( 'af/form/email/content', $content, $email, $form, $fields );
		$content = apply_filters( 'af/form/email/content/id=' . $form['post_id'], $content, $email, $form, $fields );
		$content = apply_filters( 'af/form/email/content/key=' . $form['key'], $content, $email, $form, $fields );
		
		$content = af_resolve_field_includes( $content, $fields );
		
		
		// Headers
		$headers = array();
		
		// HTML content type
		$headers[] = 'Content-type: text/html; charset=UTF-8';
		
		// From header
		$from = af_resolve_field_includes( $email['from'], $fields );
		$headers[] = 'From:' . $from;
		
		$headers = apply_filters( 'af/form/email/headers', $headers, $email, $form, $fields );
		$headers = apply_filters( 'af/form/email/headers/id=' . $form['post_id'], $headers, $email, $form, $fields );
		$headers = apply_filters( 'af/form/email/headers/key=' . $form['key'], $headers, $email, $form, $fields );
		
		
		// Attachments
		$attachments = array();
		
		$attachments = apply_filters( 'af/form/email/attachments', $attachments, $email, $form, $fields );
		$attachments = apply_filters( 'af/form/email/attachments/id=' . $form['post_id'], $attachments, $email, $form, $fields );
		$attachments = apply_filters( 'af/form/email/attachments/key=' . $form['key'], $attachments, $email, $form, $fields );
		
		
		// Send email using wp_mail
		wp_mail( $recipient, $subject, $content, $headers, $attachments );
		
	}
	
}

new AF_Core_Emails();