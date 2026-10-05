<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fdp_register_lead_post_type() {

	$labels = array(
		'name'          => 'Leads',
		'singular_name' => 'Lead',
		'menu_name'     => 'Leads',
		'all_items'     => 'All Leads',
		'add_new'       => 'Add New',
		'add_new_item'  => 'Add New Lead',
		'edit_item'     => 'Edit Lead',
		'new_item'      => 'New Lead',
		'search_items'  => 'Search Leads',
		'not_found'     => 'No leads found',
	);

	$args = array(
		'labels'       => $labels,
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'flowdesk-pro',
		'supports'     => array( 'title' ),
	);

	register_post_type( 'fdp_lead', $args );
}

add_action( 'init', 'fdp_register_lead_post_type' );

/**
 * Change Lead title placeholder.
 */
function fdp_lead_title_placeholder( $title, $post ) {

	if ( 'fdp_lead' === $post->post_type ) {
		return 'Enter lead name';
	}

	return $title;
}

add_filter(
	'enter_title_here',
	'fdp_lead_title_placeholder',
	10,
	2
);