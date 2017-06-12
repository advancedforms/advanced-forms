<?php
/*
	Plugin Name: Advanced Forms
	Description: Flexible and developer-friendly forms with the power of Advanced Custom Fields.
	Version: 1.3.2
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
	public $version = '1.2.0';
	
	
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


		// Load translations
		load_textdomain( 'advanced-forms', plugin_dir_path( __FILE__ ) . 'language/advanced-forms-' . get_locale() . '.mo' );


		// API functions
		include( plugin_dir_path( __FILE__ ) . 'api/api-helpers.php' );
		include( plugin_dir_path( __FILE__ ) . 'api/api-forms.php' );
		include( plugin_dir_path( __FILE__ ) . 'api/api-entries.php' );

		// Core functionality
		include( plugin_dir_path( __FILE__ ) . 'core/core-forms.php' );
		include( plugin_dir_path( __FILE__ ) . 'core/core-restrictions.php' );
		include( plugin_dir_path( __FILE__ ) . 'core/core-emails.php' );
		include( plugin_dir_path( __FILE__ ) . 'core/core-entries.php' );

		// ACF additions (fields, location rules, etc.)
		include( plugin_dir_path( __FILE__ ) . 'acf/acf-additions.php' );

		// Admin
		include( plugin_dir_path( __FILE__ ) . 'admin/admin-forms.php' );
		include( plugin_dir_path( __FILE__ ) . 'admin/admin-restrictions.php' );
		include( plugin_dir_path( __FILE__ ) . 'admin/admin-entries.php' );
		include( plugin_dir_path( __FILE__ ) . 'admin/admin-emails.php' );
		
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro/advanced-forms-pro.php' ) ) {
			include( plugin_dir_path( __FILE__ ) . 'pro/advanced-forms-pro.php' );
		}


		// Include assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10, 0 );


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

		wp_enqueue_script( 'af-admin-script', plugin_dir_url( __FILE__ ) .  'assets/js/admin.js', array( 'jquery' ) );

	}


	/**
	 * Enqueues admin styles
	 *
	 * @since 1.0.0
	 *
	 */
	function enqueue_admin_styles() {

		wp_enqueue_style( 'af-admin-style', plugin_dir_url( __FILE__ ) .  'assets/css/admin.css' );

	}


	/**
	 * Enqueues global styles
	 *
	 * @since 1.0.0
	 *
	 */
	function enqueue_styles() {
		wp_enqueue_style( 'af-form-style', plugin_dir_url( __FILE__ ) .  'assets/css/form.css' );

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
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_icon'				=> 'dashicons-list-view',
			'menu_position'         => 80,
			'show_in_admin_bar'     => false,
			'can_export'            => true,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'query_var'				=> false,
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
			'menu_icon'				=> 'dashicons-list-view',
			'menu_position'         => 80,
			'show_in_admin_bar'     => false,
			'can_export'            => true,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'query_var'				=> false,
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
