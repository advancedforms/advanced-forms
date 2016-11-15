<?php
	
	
/**
 * Gets all entries for the specified form
 *
 * @since 1.0.0
 *
 */
function af_get_entries( $form_id ) {
	
	$args = array(
		'post_type' => 'af_entry',
		'posts_per_page' => '-1',
 		'meta_query' => array(
	 		array(
		 		'key' => 'entry_form',
		 		'value' => $form_id,
	 		),
 		),
	);
	
	$query = new WP_Query( $args );
	
	return $query->posts;
	
}