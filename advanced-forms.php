<?php
/*
	Plugin Name: Advanced Forms
	Description: Flexible and developer-friendly forms with the power of Advanced Custom Fields.
	Version: 1.3.4
	Author: Fabian Lindfors
	Copyright: Fabian Lindfors
	Text Domain: advanced-forms
	Domain Path: /language
*/


class AF {
	
	
	/**
	 * Plugin version
	 *
	 * @since 1.2.0
	 */
	public $version = '1.3.4';
	
	
	/**
	 * Array to hold data about the previous submission
	 *
	 * @since 1.1
	 */
	public $submission;
	


	function __construct() {
	
		add_action( 'acf/init', array( $this, 'initialize_plugin' ), 1, 0 );
		add_action( 'admin_notices', array( $this, 'missing_acf_notice' ), 10, 0 );

	}


	/**
	 * Initializes the plugin and makes sure ACF is installed
	 *
	 * @since 1.0.0
	 *
	 */
	function initialize_plugin() {

		if ( ! $this->has_acf() ) {
			return;
		}
		
		
		// Setup global plugin defaults
		$this->submission = null;
		$this->show_admin = apply_filters( 'af/settings/show_admin', true );
		$this->path 			= trailingslashit( apply_filters( 'af/settings/path', plugin_dir_path( __FILE__ ) ) );
		$this->url 				= trailingslashit( apply_filters( 'af/settings/url', plugin_dir_url( __FILE__ ) ) );


		// Load translations
		load_textdomain( 'advanced-forms', $this->path . 'language/advanced-forms-' . get_locale() . '.mo' );


		// API functions
		include( $this->path . 'api/api-helpers.php' );
		include( $this->path . 'api/api-forms.php' );
		include( $this->path . 'api/api-entries.php' );

		// Core functionality
		include( $this->path . 'core/core-forms.php' );
		include( $this->path . 'core/core-restrictions.php' );
		include( $this->path . 'core/core-emails.php' );
		include( $this->path . 'core/core-entries.php' );

		// ACF additions (fields, location rules, etc.)
		include( $this->path . 'acf/acf-additions.php' );
		include( $this->path . 'acf/fields/field_select.php' );
		include( $this->path . 'acf/fields/divider.php' );

		// Admin
		if ( $this->show_admin ) {
			include( $this->path . 'admin/admin-forms.php' );
			include( $this->path . 'admin/admin-restrictions.php' );
			include( $this->path . 'admin/admin-entries.php' );
			include( $this->path . 'admin/admin-emails.php' );
		}
		
		if ( file_exists( $this->path . 'pro/advanced-forms-pro.php' ) ) {
			include( $this->path . 'pro/advanced-forms-pro.php' );
		}


		// Include assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10, 0 );

		if ( $this->show_admin ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ), 10, 0 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10, 0 );
		}


		// Register basic post types
		add_action( 'init', array( $this, 'register_post_types' ), 10, 0 );


		// Action used to register forms
		do_action( 'af/register_forms' );

	}
	
	
	/**
	 * Check if ACF Pro is installed
	 *
	 * @since 1.3.1
	 *
	 */
	function has_acf() {
		
		return class_exists( 'acf_pro' );
		
	}


	/**
	 * Display notice if ACF Pro is missing
	 *
	 * @since 1.0.0
	 */
	function missing_acf_notice() {
		
		if ( ! $this->has_acf() ) {
			
			echo sprintf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', 'Couldn\'t find ACF 5. Advanced Forms requires ACF 5 to function correctly.' );
			
		}

	}


	/**
	 * Enqueues admin scripts
	 *
	 * @since 1.0.1
	 *
	 */
	function enqueue_admin_scripts() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'af-admin-script', $this->url .  'assets/js/admin.js', array( 'jquery' ) );

	}


	/**
	 * Enqueues admin styles
	 *
	 * @since 1.0.0
	 *
	 */
	function enqueue_admin_styles() {

		wp_enqueue_style( 'af-admin-style', $this->url .  'assets/css/admin.css' );

	}


	/**
	 * Enqueues global styles
	 *
	 * @since 1.0.0
	 *
	 */
	function enqueue_styles() {

		wp_enqueue_style( 'af-form-style', $this->url .  'assets/css/form.css' );

	}


	/**
	 * Register custom post types, forms and entries
	 *
	 * @since 1.0.0
	 */
	function register_post_types() {

		// Form post type
		$labels = array(
			'name'                  => _x( 'Forms', 'Post Type General Name', 'advanced-forms' ),
			'singular_name'         => _x( 'Form', 'Post Type Singular Name', 'advanced-forms' ),
			'menu_name'             => __( 'Forms', 'advanced-forms' ),
			'name_admin_bar'        => __( 'Form', 'advanced-forms' ),
			'archives'              => __( 'Item Archives', 'advanced-forms' ),
			'parent_item_colon'     => __( 'Parent Item:', 'advanced-forms' ),
			'all_items'             => __( 'Forms', 'advanced-forms' ),
			'add_new_item'          => __( 'Add New Form', 'advanced-forms' ),
			'add_new'               => __( 'Add New', 'advanced-forms' ),
			'new_item'              => __( 'New Form', 'advanced-forms' ),
			'edit_item'             => __( 'Edit Form', 'advanced-forms' ),
			'update_item'           => __( 'Update Form', 'advanced-forms' ),
			'view_item'             => __( 'View Form', 'advanced-forms' ),
			'search_items'          => __( 'Search Form', 'advanced-forms' ),
			'not_found'             => __( 'Not found', 'advanced-forms' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'advanced-forms' ),
			'featured_image'        => __( 'Featured Image', 'advanced-forms' ),
			'set_featured_image'    => __( 'Set featured image', 'advanced-forms' ),
			'remove_featured_image' => __( 'Remove featured image', 'advanced-forms' ),
			'use_featured_image'    => __( 'Use as featured image', 'advanced-forms' ),
			'insert_into_item'      => __( 'Insert into form', 'advanced-forms' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'advanced-forms' ),
			'items_list'            => __( 'Forms list', 'advanced-forms' ),
			'items_list_navigation' => __( 'Forms list navigation', 'advanced-forms' ),
			'filter_items_list'     => __( 'Filter forms list', 'advanced-forms' ),
		);
		$args = array(
			'label'                 => __( 'Form', 'advanced-forms' ),
			'description'           => __( 'Form', 'advanced-forms' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => $this->show_admin,
			'show_in_menu'          => $this->show_admin,
			'menu_icon'							=> 'dashicons-list-view',
			'menu_position'         => 80,
			'show_in_admin_bar'     => false,
			'can_export'            => true,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'query_var'							=> false,
		);
		register_post_type( 'af_form', $args );


		// Entry post type
		$labels = array(
			'name'                  => _x( 'Entries', 'Post Type General Name', 'advanced-forms' ),
			'singular_name'         => _x( 'Entry', 'Post Type Singular Name', 'advanced-forms' ),
			'menu_name'             => __( 'Entries', 'advanced-forms' ),
			'name_admin_bar'        => __( 'Entry', 'advanced-forms' ),
			'archives'              => __( 'Entry Archives', 'advanced-forms' ),
			'parent_item_colon'     => __( 'Parent Entry:', 'advanced-forms' ),
			'all_items'             => __( 'Entries', 'advanced-forms' ),
			'add_new_item'          => __( 'Add New Entry', 'advanced-forms' ),
			'add_new'               => __( 'Add New', 'advanced-forms' ),
			'new_item'              => __( 'New Entry', 'advanced-forms' ),
			'edit_item'             => __( 'Edit Entry', 'advanced-forms' ),
			'update_item'           => __( 'Update Entry', 'advanced-forms' ),
			'view_item'             => __( 'View Entry', 'advanced-forms' ),
			'search_items'          => __( 'Search Entry', 'advanced-forms' ),
			'not_found'             => __( 'Not found', 'advanced-forms' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'advanced-forms' ),
			'featured_image'        => __( 'Featured Image', 'advanced-forms' ),
			'set_featured_image'    => __( 'Set featured image', 'advanced-forms' ),
			'remove_featured_image' => __( 'Remove featured image', 'advanced-forms' ),
			'use_featured_image'    => __( 'Use as featured image', 'advanced-forms' ),
			'insert_into_item'      => __( 'Insert into entry', 'advanced-forms' ),
			'uploaded_to_this_item' => __( 'Uploaded to this entries', 'advanced-forms' ),
			'items_list'            => __( 'Entries list', 'advanced-forms' ),
			'items_list_navigation' => __( 'Entries list navigation', 'advanced-forms' ),
			'filter_items_list'     => __( 'Filter entries list', 'advanced-forms' ),
		);
		$args = array(
			'label'                 => __( 'Entry', 'advanced-forms' ),
			'description'           => __( 'Entry', 'advanced-forms' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => 'edit.php?post_type=af_form',
			'menu_icon'							=> 'dashicons-list-view',
			'menu_position'         => 80,
			'show_in_admin_bar'     => false,
			'can_export'            => true,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'query_var'							=> false,
		);
		register_post_type( 'af_entry', $args );

	}

}


/**
 * Helper function to access the global AF object
 * 
 * @since 1.1
 */
function AF() {
	
	global $af;
	
	if ( ! isset( $af ) ) {
		$af = new AF();
	}
	
	return $af;
	
}

// Initalize plugin
AF();
