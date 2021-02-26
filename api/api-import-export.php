<?php

/**
 * Prepares a form object for export.
 *
 * @since 1.7.0
 *
 */
function af_export_form( $form ) {
  unset( $form['post_id'] );
  return $form;
}


/**
 * Imports a form object to a form post.
 * A new post will be generated if none exists with the specific key.
 *
 * @since 1.7.0
 *
 */
function af_import_form( $form ) {
  $post = af_form_post_from_key( $form['key'] );

  $post_id = NULL;
  if ( ! $post ) {
    $post_id = wp_insert_post(array(
      'post_title' => '',
      'post_type' => 'af_form',
      'post_status' => 'publish',
    ));

    if ( is_wp_error( $post_id ) ) {
      return false;
    }
  } else {
    $post_id = $post->ID;
  }

  return af_form_to_post( $form, $post_id );
}