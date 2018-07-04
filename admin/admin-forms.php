<?php
	
/**
 * Handles the admin part of the forms
 *
 * @since 1.0.0
 *
 */
class AF_Admin_Forms {
	
	function __construct() {
		
		// Actions
		add_action( 'admin_init', array( $this, 'add_fields_meta_box' ), 10, 0 );
		add_action( 'edit_form_after_title', array( $this, 'display_form_key' ), 10, 0 );
		add_action( 'save_post', array( $this, 'add_form_key' ), 10, 3 );
		add_action( 'acf/init', array( $this, 'register_fields' ), 10, 0 );
		add_action( 'media_buttons', array( $this, 'add_wysiwyg_content_field_inserter' ), 10, 1 );
		add_action( 'admin_footer', array( $this, 'add_forms_sidebar' ), 10, 0 );

		add_action( 'post_submitbox_start', array( $this, 'add_actions' ), 10, 1 );
		
		
		// Filters
		add_filter( 'manage_af_form_posts_columns', array( $this, 'manage_columns' ), 10, 1 );
		add_action( 'manage_af_form_posts_custom_column', array( $this, 'custom_columns_content' ), 10, 2 );
		
	}
	
	
	
	/**
	 * Adds a form key to a form if one doesn't exist
	 * 
	 * @since 1.0.0
	 *
	 */
	function add_form_key( $post_id, $post, $update ) {
		
		if ( 'af_form' == $post->post_type && ! get_post_meta( $post->ID, 'form_key', true ) ) {
			
			$form_key = 'form_' . uniqid();
			
			update_post_meta( $post->ID, 'form_key', $form_key );
			
		}
		
	}
	
	
	/**
	 * Displays the form key after the title
	 *
	 * @since 1.0.0
	 *
	 */
	function display_form_key() {
		
		global $post;
		
		if ( 'af_form' == $post->post_type && $form_key = get_post_meta( $post->ID, 'form_key', true ) ) {
			
			echo '<div id="edit-slug-box">';
			
			echo sprintf( '<strong>%s </strong>%s', __( 'Form key:', 'advanced-forms' ), $form_key );
			
			echo '</div>';
			
		}
		
	}
	
	
	/**
	 * Adds a meta box to the form edit page used to display the form fields
	 * 
	 * @since 1.0.0
	 *
	 */
	function add_fields_meta_box() {
		
		add_meta_box( 'af_form_fields', __( 'Fields', 'advanced-forms' ), array( $this, 'fields_meta_box_callback' ), 'af_form', 'normal', 'default', null );
		
	}
	
	
	/**
	 * Callback for the fields meta box
	 * Displays all fields registered to the current form
	 *
	 * @since 1.0.0
	 *
	 */
	function fields_meta_box_callback() {
		
		global $post;
		
		$form = af_get_form( $post->ID );
		
		// Get field groups for the current form
		$field_groups = af_get_form_field_groups( $form['key'] );
		
		?>
		
		<p><?php _e( 'Add fields by setting the location of your fields group to this form.', 'advanced-forms' ); ?></p>
		
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Label', 'advanced-forms' ) ?></th>
					<th scope="col"><?php _e( 'Name', 'advanced-forms' ) ?></th>
					<th scope="col"><?php _e( 'Type', 'advanced-forms' ) ?></th>
				</tr>
			</thead>
			
			<tbody>
				
				<?php if ( ! empty( $field_groups ) ) : ?>
				
					<?php foreach ( $field_groups as $field_group ) : ?>
					
						<?php
							// Get all fields for this field group
							$fields = acf_get_fields( $field_group );	
						?>
						
						<tr class="field-group-heading">
							<td colspan="3">
								<a href="<?php echo get_edit_post_link( $field_group['ID'] ); ?>"><?php echo $field_group['title']; ?></a>
							</td>
						</tr>
											
						<?php foreach ( $fields as $field ) : ?>
							
							<tr>
								<td><?php echo $field['label']; ?></td>
								<td><?php echo $field['name']; ?></td>
								<td><?php echo acf_get_field_type_label( $field['type'] ); ?></td>
							</tr>
						
						<?php endforeach; ?>
						
					<?php endforeach; ?>
					
				<?php else: ?>
				
					<tr>
						<td colspan="3"><?php _e( 'No field groups connected to this form', 'advanced-forms' ); ?></td>
					</tr>
				
				<?php endif; ?>
				
			</tbody>
		</table>
		<?php
	}
	
	
	/**
	 * Adds custom columns to the listings page
	 *
	 * @since 1.0.0
	 *
	 */
	function manage_columns( $columns ) {
		
		$new_columns = array(
			'key'		=> __( 'Key', 'advanced-forms' ),
			'fields' 	=> __( 'Fields', 'advanced-forms' ),
			'entries' 	=> __( 'Entries', 'advanced-forms' ),
		);

		// Remove date column
		unset( $columns['date'] );
		
		return array_merge( array_splice( $columns, 0, 2 ), $new_columns, $columns );
		
	}
	
	
	/**
	 * Outputs the content for the custom columns
	 *
	 * @since 1.0.0
	 *
	 */
	function custom_columns_content( $column, $post_id ) {
		
		$form = af_get_form( $post_id );
		
		if ( 'key' == $column ) {
			
			echo get_post_meta( $post_id, 'form_key', true );
			
		} if ( 'fields' == $column ) {
			
			$count = 0;
			$field_groups = af_get_form_field_groups( $form['key'] );
			
			// Count the number of fields in all field groups
			foreach ( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );
				$count += count( $fields );
				
			}
			
			echo $count;
			
		} elseif ( 'entries' == $column ) {
			
			$entries = af_get_entries( $form['key'] );
			
			echo sprintf( '<a href="%s">%s</a>', admin_url() . '/edit.php?post_type=af_entry&entry_form=' . $form['key'], count( $entries ) );
			
		}
		
	}
	
	
	/**
	 * Add an "Insert field" button to WYSIWYG fields
	 *
	 * @since 1.0.0
	 *
	 */
	function add_wysiwyg_content_field_inserter( $id ) {
		
		global $post;
		
		if ( ! $post ) {
			return;
		}
		
		
		$form = af_form_from_post( $post );
		
		if ( ! $form ) {
			return;
		}
		
		
		if ( 'acf-editor' == substr($id, 0, 10) ) {
			
			_af_field_inserter_button( $form, 'all', false );
			
		}
		
		
	}


	/**
	 * Outputs the sidebar on the forms list page
	 * Moved up by Javascript
	 *
	 * @since 1.5.0
	 *
	 */
	function add_forms_sidebar() {
		$title = AF()->pro ? 'Advanced Forms Pro' : 'Advanced Forms';
		$doc_url = 'https://advancedforms.github.io';
		$pro_url = 'https://hookturn.io/downloads/advanced-forms';
		$icon = '<i aria-hidden="true" class="dashicons dashicons-external"></i>';
		?>
		<script type="text/html" id="af-sidebar-template">
		<div class="acf-column-2 af-sidebar">
			<div class="acf-box">
				<div class="inner">
					<h2><?php echo $title; ?></h2>

					<h3><?php _e( 'Resources','advanced-forms' ); ?></h3>
					<ul>
						<li><a href="<?php echo $doc_url; ?>#guides"><?php echo $icon; ?> <?php _e( 'Guides', 'advanced-forms' ); ?></a></li>
						<li><a href="<?php echo $doc_url; ?>#actions"><?php echo $icon; ?> <?php _e( 'Actions', 'advanced-forms' ); ?></a></li>
						<li><a href="<?php echo $doc_url; ?>#filters"><?php echo $icon; ?> <?php _e( 'Filters', 'advanced-forms' ); ?></a></li>
					</ul>

					<?php if ( ! AF()->pro ) : ?>
					<h3><?php _e( 'Pro','advanced-forms' ); ?></h3>
					<ul class="feature-list">
						<li><?php _e( 'Create/edit posts and users', 'advanced-forms' ); ?></li>
						<li><?php _e( 'Integrate with Slack, Mailchimp, and Zapier', 'advanced-forms' ); ?></li>
						<li><?php _e( 'Get direct, priority support', 'advanced-forms' ); ?></li>
					</ul>
					<a href="<?php echo $pro_url; ?>"><?php echo $icon; ?> <?php _e( 'Available from hookturn.io', 'advanced-forms' ); ?></a>
					<?php endif; ?>

					<h3><?php _e( 'Support','advanced-forms' ); ?></h3>
					<p>
						<?php _e( 'Issues, questions, or suggestions?', 'advanced-forms' ); ?>
						<?php if ( AF()->pro ) : ?>
						<?php _e( 'Contact us directly', 'advanced-forms' ); ?><a href="https://hookturn.io/contact/"></a> <?php _e( 'for priority support', 'advanced-forms' ); ?>.
						<?php else : ?>
						<?php _e( 'Create a ticket on the', 'advanced-forms' ); ?> <a href="https://wordpress.org/support/plugin/advanced-forms"><?php _e( 'Wordpress support forums', 'advanced-forms' ); ?></a>.
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>
		</script>
		<?php
	}


	/**
	 * Adds an wrapper for action buttons to the publish box.
	 * Refactored out of admin-preview.php.
	 *
	 * @since 1.5.0
	 *
	 */
	function add_actions( $post ) {

		if ( is_null( $post ) || 'af_form' != $post->post_type ) {
      return;
    }

    echo '<div class="af-form-actions-wrapper">';

    do_action( 'af/admin/form/actions', $post->ID );

    echo '</div>';

	}


	/**
	 * Registers the form settings fields
	 *
	 * @since 1.0.0
	 *
	 */
	function register_fields() {
		
		$settings_field_group = array (
			'key' => 'group_form_settings',
			'title' => __( 'Form settings', 'advanced-forms' ),
			'fields' => array (
				array (
					'key' => 'field_form_display_tab',
					'label' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Display', 'advanced-forms' ),
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
				),
				array (
					'key' => 'field_form_description',
					'label' => __( 'Description', 'advanced-forms' ),
					'name' => 'form_description',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_form_success_message',
					'label' => __( 'Success message', 'advanced-forms' ),
					'name' => 'form_success_message',
					'type' => 'wysiwyg',
					'instructions' => __( 'The message displayed after a successful submission.', 'advanced-forms' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_form_statistics_tab',
					'label' => '<span class="dashicons dashicons-chart-bar"></span>' . __( 'Statistics', 'advanced-forms' ),
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
				),
				array (
					'key' => 'field_form_num_of_submissions',
					'label' => __( 'Number of submissions', 'advanced-forms' ),
					'name' => 'form_num_of_submissions',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'default_value' => 0,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
					'readonly' => true,
				),
				array (
					'key' => 'field_form_num_of_views',
					'label' => __( 'Number of times viewed', 'advanced-forms' ),
					'name' => 'form_num_of_views',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'default_value' => 0,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
					'readonly' => true,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'af_form',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		);
		
		
		$settings_field_group = apply_filters( 'af/form/settings_fields', $settings_field_group );
		
		acf_add_local_field_group( $settings_field_group );
		
	}
	
}

return new AF_Admin_Forms();