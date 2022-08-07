<?php
	
class AF_Core_Emails {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'send_form_emails' ), 15, 3 );
		add_action( 'af/emails/send_form_email', array( $this, 'send_single_form_email' ), 10, 4 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		add_action( 'af/form/to_post', array( $this, 'form_to_post' ), 10, 2 );
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


	function form_to_post( $form, $post ) {
		if ( $form['emails'] ) {
			update_field( 'field_form_emails', $form['emails'], $post->ID );
		}
	}
	
	
	/**
	 * Send form emails after submission
	 *
	 * @since 1.0.0
	 *
	 */
	function send_form_emails( $form, $fields, $args ) {
		
		$emails = $form['emails'];
		
		if ( $emails && ! empty( $emails ) ) {
			
			foreach ( $emails as $email ) {
				
				do_action( 'af/emails/send_form_email', $email, $form, $fields, $args );
				
			}
			
		}
		
	}
	
	
	/**
	 * Send single form email
	 *
	 * @since 1.0.0
	 *
	 */
	function send_single_form_email( $email, $form, $fields, $args ) {
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

		// Bail early if there are no recipients
		// This allows emails to be stopped by returning false from the filter
		if ( $recipient === false ) {
			return;
		}
		
		// Subject line
		$subject = $email['subject'];
		$subject = apply_filters( 'af/form/email/subject', $subject, $email, $form, $fields );
		$subject = apply_filters( 'af/form/email/subject/id=' . $form['post_id'], $subject, $email, $form, $fields );
		$subject = apply_filters( 'af/form/email/subject/key=' . $form['key'], $subject, $email, $form, $fields );

		$subject = af_resolve_merge_tags( $subject, $fields );
		
		
		// Email contents
		$content = apply_filters( 'the_content', $email['content'] );
		$content = apply_filters( 'af/form/email/content', $content, $email, $form, $fields );
		$content = apply_filters( 'af/form/email/content/id=' . $form['post_id'], $content, $email, $form, $fields );
		$content = apply_filters( 'af/form/email/content/key=' . $form['key'], $content, $email, $form, $fields );
		
		$content = af_resolve_merge_tags( $content, $fields );
		
		// Add default HTML template
		$use_template = true;
		$use_template = apply_filters( 'af/form/email/use_template', $use_template, $email, $form, $args );
		$use_template = apply_filters( 'af/form/email/use_template/id=' . $form['post_id'], $use_template, $email, $form, $args );
		$use_template = apply_filters( 'af/form/email/use_template/key=' . $form['key'], $use_template, $email, $form, $args );
		
		if ( $use_template ) {
			$content = sprintf(
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
				<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
						<title>%s</title>
						<style>%s</style>
					</head>
					<body>
						%s
					</body>
				</html>',
				$subject,
				$this->get_email_style( $email, $form ),
				$content
			);
		}
		
		
		// Headers
		$headers = array();
		
		// HTML content type
		$content_type = 'text/html; charset=UTF-8';
		$content_type = apply_filters( 'af/form/email/content_type', $content_type, $email, $form, $args );
		$content_type = apply_filters( 'af/form/email/content_type/id=' . $form['post_id'], $content_type, $email, $form, $args );
		$content_type = apply_filters( 'af/form/email/content_type/key=' . $form['key'], $content_type, $email, $form, $args );
		$headers[] = sprintf( 'Content-type: %s', $content_type );
		
		// From header
		$from = af_resolve_merge_tags( $email['from'], $fields );
		$headers[] = 'From:' . $from;
		
		$headers = apply_filters( 'af/form/email/headers', $headers, $email, $form, $fields );
		$headers = apply_filters( 'af/form/email/headers/id=' . $form['post_id'], $headers, $email, $form, $fields );
		$headers = apply_filters( 'af/form/email/headers/key=' . $form['key'], $headers, $email, $form, $fields );
		
		
		// Attachments
		$attachments = array();
		
		$attachments = apply_filters( 'af/form/email/attachments', $attachments, $email, $form, $fields );
		$attachments = apply_filters( 'af/form/email/attachments/id=' . $form['post_id'], $attachments, $email, $form, $fields );
		$attachments = apply_filters( 'af/form/email/attachments/key=' . $form['key'], $attachments, $email, $form, $fields );
		
		
		do_action( 'af/email/before_send', $email, $form );
		do_action( 'af/email/before_send/id=' . $form['post_id'], $email, $form );
		do_action( 'af/email/before_send/key=' . $form['key'], $email, $form );
		
		// Arrayify recipients
		if ( is_array( $recipient ) ) {
			$recipients = array_unique( $recipient );
		} else {
			$recipients = array( af_resolve_merge_tags( $recipient, $fields ) );
		}

		// Send separate emails to all recipients
		foreach ( $recipients as $recipient ) {
			wp_mail( $recipient, $subject, $content, $headers, $attachments );
		}
		
		do_action( 'af/email/after_send', $email, $form );
		do_action( 'af/email/after_send/id=' . $form['post_id'], $email, $form );
		do_action( 'af/email/after_send/key=' . $form['key'], $email, $form );
		
	}
	
	
	/**
	 * Returns styles for email
	 *
	 * @since 1.2.0
	 *
	 */
	function get_email_style( $email, $form ) {

		ob_start();
		?>
		
		body {
			font-family: sans-serif;
		}
		
		table {
			border-collapse: collapse;
			width: 100%;
			max-width: 500px;
			text-align: left;
		}
		
		th,
		td {
			border: 1px solid #ccc;
		}
		
		th {
			background-color: #fafafa;
			padding: 10px;
		}
		
		td {
			padding: 15px 20px;
		}
		
		.af-field-include-repeater td {
			padding: 10px;
		}
		
		<?php
		$styles = ob_get_clean();
		
		$styles = apply_filters( 'af/form/email/styles', $styles, $email, $form );
		$styles = apply_filters( 'af/form/email/styles/id=' . $form['post_id'], $styles, $email, $form );
		$styles = apply_filters( 'af/form/email/styles/key=' . $form['key'], $styles, $email, $form );
		
		return $styles;
		
	}
	
}

return new AF_Core_Emails();
