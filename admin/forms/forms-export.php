<?php

class AF_Admin_Forms_Export {

  function __construct() {

    add_action( 'admin_menu', array( $this, 'register_admin_page' ), 10, 0 );
    add_action( 'af/admin/form/actions', array( $this, 'add_export_button' ), 15, 1 );
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
    $code = $this->generate_form_code( $form );
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
    $export_url = add_query_arg( 'form_id', $form_id, menu_page_url( 'af_export_form', false ) );
    echo sprintf( '<a href="%s" class="button button-large">%s</a>', $export_url, __( 'Export', 'advanced-forms' ) );
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


  function generate_form_code( $form ) {

    // Remove post ID key
    unset( $form['post_id'] );

    $str_replace = array(
      "  "      => "\t",
      "'!!__(!!\'"  => "__('",
      "!!\', !!\'"  => "', '",
      "!!\')!!'"    => "')",
      "array ("   => "array("
    );
    $preg_replace = array(
      '/([\t\r\n]+?)array/' => 'array',
      '/[0-9]+ => array/'   => 'array'
    );

    // Create a var_export string of the form array
    $code = var_export( $form, true );
      
    // change double spaces to tabs
    $code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
    
    // correctly formats "=> array("
    $code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
    
    $code = esc_textarea( $code );

    $output  = "function register_forms() {\n";
    $output .= sprintf( "\taf_register_form( %s );\n", str_replace( "\n", "\n\t", $code ) );
    $output .= "}\n";
    $output .= "add_action( 'af/register_forms', 'register_forms' );";

    return $output;

  }

}

return new AF_Admin_Forms_Export();