<?php

class AF_Admin_Forms_Export {

  function __construct() {

    add_action( 'admin_menu', array( $this, 'register_admin_page' ), 10, 0 );
    add_action( 'af/admin/form/actions', array( $this, 'add_export_button' ), 15, 1 );
    add_action( 'admin_footer-post.php', array( $this, 'add_export_modal' ), 10, 0 );
    add_filter( 'admin_title', array( $this, 'fix_admin_title' ), 10, 2 );

  }


  /**
   * Register hidden admin page for form export.
   *
   * @since 1.5.0
   *
   */
  function register_admin_page() {

    // By using add_submenu_page without a parent the page won't be shown in the admin menu.
    add_submenu_page( null, 'Export form', 'Export', 'edit_pages', 'af_export_form', array( $this, 'export_page' ) );

  }


  /**
   * Display form export page
   *
   * @since 1.5.0
   *
   */
  function export_page() {

    if ( ! isset( $_GET['form_id'] ) ) {
      return;
    }

    $form = af_form_from_post( $_GET['form_id'] );
    $form_link = get_edit_post_link( $form['post_id'] );
    $code = AF_Core_Forms_Export::generate_form_code( $form['post_id'] );
    ?>

    <div class="af-form-export wrap">
      <h1 class="wp-heading-inline"><?php _e( 'Exporting', 'advanced-forms' ); ?> "<?php echo $form['title']; ?>"</h1>
      <a href="<?php echo $form_link; ?>" class="page-title-action"><?php _e( 'Back to form', 'advanced-forms' ) ?></a>
      <hr class="wp-header-end" />

      <div id="poststuff">
        <div class="postbox">
          <div class="inside">
            <p>
              <?php _e( 'Place the following code in your theme or plugin to register your form programmatically.', 'advanced-forms' ); ?>
              <button class="copy-button button button-small" data-copied-text="Copied!">Copy to clipboard</button>    
            </p>
            <pre class="export-code"><?php echo $code; ?></pre>
          </div>
        </div>
      </div>
    </div>

    <?php
  }


  function add_export_button( $form_id ) {
    add_thickbox();
    $link = '#TB_inline&width=500&inlineId=form-export-modal&height=400';
    echo sprintf( '<a href="%s" class="button button-large thickbox" title="Export form">%s</a>', $link, __( 'Export', 'advanced-forms' ) );

  }


  function add_export_modal() {
    global $post;

    if ( empty( $post ) || 'af_form' != $post->post_type) {
      return;
    }

    $code = AF_Core_Forms_Export::generate_form_code( $post->ID );
    ?>
    <div id="form-export-modal" style="display: none;">
      <div class="form-export">
        <pre><?php echo $code; ?></pre>
      </div>
    </div>
    <?php
  }


  /**
   * Wordpress won't display a page title, probably of the submenu hack in register_admin_page.
   * This function is hooked to the admin_title filter and fixes the issue.
   *
   * @since 1.5.0
   *
   */
  function fix_admin_title( $admin_title, $title ) {
    if ( get_current_screen()->id != 'admin_page_af_export_form' ) {
      return $admin_title;
    }

    return __( 'Export form', 'advanced-forms' ) . $admin_title;
  }

}

return new AF_Admin_Forms_Export();