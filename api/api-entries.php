<?php


/**
 * Gets all entries for the specified form
 *
 * @since 1.0.0
 *
 */
function af_get_entries( $form_key ) {

	$args = array(
		'post_type' => 'af_entry',
		'posts_per_page' => '-1',
 		'meta_query' => array(
	 		array(
		 		'key' => 'entry_form',
		 		'value' => $form_key,
	 		),
 		),
	);

	$query = new WP_Query( $args );

	return $query->posts;

}


/**
 * Get number of entries by form
 *
 * 1.0.2
 *
 */
function af_get_entry_count( $form_key ) {
	
	$args = array(
		'post_type' => 'af_entry',
		'posts_per_page' => '-1',
 		'meta_query' => array(
	 		array(
		 		'key' => 'entry_form',
		 		'value' => $form_key,
	 		),
 		),
	);

	$query = new WP_Query( $args );
	
	return $query->found_posts;
	
}