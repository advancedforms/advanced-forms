<?php

class AF_Admin_Forms_Preview {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ), 10, 0 );
		add_action( 'af/admin/form/actions', array( $this, 'add_preview_button' ), 10, 1 );
		add_filter( 'admin_title', array( $this, 'fix_admin_title' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_acf' ), 10, 1 );

		add_filter( 'af/form/button_attributes', array( $this, 'add_classes_to_button' ), 10, 1 );
		add_filter( 'af/form/previous_button_atts', array( $this, 'add_classes_to_page_buttons' ), 10, 1 );
		add_filter( 'af/form/next_button_atts', array( $this, 'add_classes_to_page_buttons' ), 10, 1 );
	}

	/**
	 * Register hidden admin page for form previews.
	 *
	 * @since 1.4.0
	 */
	function register_admin_page() {
		// By using add_submenu_page without a parent the page won't be shown in the admin menu.
		add_submenu_page(
			'admin.php',
			'Preview form',
			'Preview',
			'edit_pages',
			'af_preview_form',
			[ $this, 'preview_page' ]
		);
	}

	/**
	 * Display form preview page
	 *
	 * @since 1.4.0
	 */
	function preview_page() {
		if ( ! isset( $_GET['form_id'] ) ) {
			return;
		}

		$form = af_form_from_post( $_GET['form_id'] );

		echo '<div class="af-form-preview wrap">';

		echo sprintf( '<h1 class="wp-heading-inline">%s "%s"</h1>', __( 'Previewing', 'advanced-forms' ), $form['title'] );
		echo sprintf( '<a href="%s" class="page-title-action">%s</a>', get_edit_post_link( $form['post_id'] ), __( 'Back to form', 'advanced-forms' ) );
		echo '<hr class="wp-header-end">';

		if ( af_has_submission() && ! af_submission_failed() ) {
			echo '<div id="message" class="notice notice-success">';
			echo sprintf( '<p>%s. <a href="%s">%s</a></p>', __( 'Form successfully submitted', 'advanced-forms' ), '', __( 'Test again', 'advanced-forms' ) );
			echo '</div>';
		}

		echo '<div id="poststuff">';
		echo '<div class="postbox acf-postbox">';
		echo '<div class="inside acf-fields">';

		advanced_form( $form['post_id'] );

		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Wordpress won't display a page title, probably of the submenu hack in register_admin_page.
	 * This function is hooked to the admin_title filter and fixes the issue.
	 *
	 * @since 1.4.0
	 */
	function fix_admin_title( $admin_title, $title ) {
		if ( get_current_screen()->id != 'admin_page_af_preview_form' ) {
			return $admin_title;
		}

		return __( 'Preview form', 'advanced-forms' ) . $admin_title;
	}

	/**
	 * We need to enqueue ACF early for it to be included in the head.
	 * ACF needs to be enqueued early in the admin panel.
	 *
	 * @since 1.5.3
	 */
	function enqueue_acf( $page ) {
		if ( 'admin_page_af_preview_form' != $page ) {
			return;
		}

		acf_enqueue_scripts();
	}

	/**
	 * Add preview button to form publish box
	 *
	 * @since 1.4.0
	 */
	function add_preview_button( $form_id ) {
		$test_url = add_query_arg( 'form_id', $form_id, menu_page_url( 'af_preview_form', false ) );
		echo sprintf( '<a href="%s" class="button button-large">%s</a>', $test_url, __( 'Preview', 'advanced-forms' ) );
	}

	/**
	 * Add WP button classes to form submit buttons in admin
	 *
	 * @since 1.4.0
	 */
	function add_classes_to_button( $attributes ) {
		if ( ! is_admin() ) {
			return $attributes;
		}

		$attributes['class'] .= ' button button-primary button-large';

		return $attributes;
	}

	/**
	 * Add WP button classes to multi-page form buttons
	 *
	 * @since 1.5.0
	 */
	function add_classes_to_page_buttons( $attributes ) {
		if ( ! is_admin() ) {
			return $attributes;
		}

		$attributes['class'] .= ' button button-large';

		return $attributes;
	}

}

return new AF_Admin_Forms_Preview();