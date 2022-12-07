<?php

class AF_Admin_Forms_Import {
  function __construct() {
    add_action( 'admin_menu', array( $this, 'register_admin_page' ), 5, 0 );
    add_action( 'admin_init', array( $this, 'import_json_file' ), 10, 0 );
    add_filter( 'admin_title', array( $this, 'fix_admin_title' ), 10, 2 );
  }

  /**
   * Register hidden admin page for form import.
   *
   * @since 1.7.0
   *
   */
  function register_admin_page() {
    // By using add_submenu_page without a parent the page won't be shown in the admin menu.
    add_submenu_page( 'edit.php?post_type=af_form', __( 'Import form', 'advanced-forms '), __( 'Import', 'advanced-forms' ), 'edit_pages', 'af_import_form', array( $this, 'import_form' ) );
  }


  /**
   * Display form import page.
   *
   * @since 1.7.0
   *
   */
  function import_form() {
    ?>
    <div class="af-form-import wrap">
      <h1><?php _e( 'Import form', 'advanced-forms' ); ?></h1>
      <hr class="wp-header-end" />

      <div id="poststuff" class="af-postbox-wrapper">
        <div class="postbox af-postbox-left">
          <h2 class="hndle"><span>Import JSON file</span></h2>
          <div class="inside">
            <form method="post" enctype="multipart/form-data">
              <p>
                <?php _e( 'Select the form JSON file you would like to import. If a form with the same key already exists it will be overwritten.', 'advanced-forms' ); ?>
              </p>

              <div class="acf-fields">
                <?php
                  acf_render_field_wrap(array(
                    'label'   => __( 'Select File', 'advanced-forms' ),
                    'type'    => 'file',
                    'name'    => 'af_import_json_file',
                    'value'   => false,
                    'uploader'  => 'basic',
                  ));
                ?>
              </div>

              <input type="submit" class="button button-primary" name="af_import_json" value="<?php _e( 'Import File', 'advanced-forms' ); ?>" />
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php
  }


  function import_json_file() {
    if ( ! isset( $_POST['af_import_json'] ) ) {
      return;
    }

    if ( empty( $_FILES['af_import_json_file']['size'] ) ) {
      return acf_add_admin_notice( __( 'No files selected', 'advanced-forms' ) );
    }

    $file = $_FILES['af_import_json_file'];
    $json = file_get_contents( $file['tmp_name'] );
    $json = json_decode( $json, true );

    $post = af_import_form( $json );
    if ( $post ) {
	  /* translators: The ACF field key */
      $message = sprintf( __( 'Form with key %s imported.', 'advanced-forms' ), $json['key'] );
      $link = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $post ), __( 'Edit form', 'advanced-forms' ) );
      acf_add_admin_notice( $message . ' ' . $link, 'success' );
    } else {
      acf_add_admin_notice( __( 'Failed to import form', 'advanced-forms' ) );
    }
  }

  /**
   * Wordpress won't display a page title, probably because of the submenu hack in register_admin_page.
   * This function is hooked to the admin_title filter and fixes the issue.
   *
   * @since 1.6.5
   *
   */
  function fix_admin_title( $admin_title, $title ) {
    if ( get_current_screen()->id != 'admin_page_af_import_form' ) {
      return $admin_title;
    }

    return __( 'Import form', 'advanced-forms' ) . $admin_title;
  }
  
}

return new AF_Admin_Forms_Import();