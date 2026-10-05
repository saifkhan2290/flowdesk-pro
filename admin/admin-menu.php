<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register FlowDesk Pro admin menu.
 */
function fdp_register_admin_menu() {

	add_menu_page(
		'FlowDesk Pro',
		'FlowDesk Pro',
		'manage_options',
		'flowdesk-pro',
		'fdp_render_dashboard',
		'dashicons-groups',
		25
	);


	/**
	 * Dashboard submenu.
	 */
	add_submenu_page(
		'flowdesk-pro',
		'Dashboard',
		'Dashboard',
		'manage_options',
		'flowdesk-pro',
		'fdp_render_dashboard'
	);


	/**
	 * Pipeline submenu.
	 */
	add_submenu_page(
		'flowdesk-pro',
		'Pipeline',
		'Pipeline',
		'manage_options',
		'flowdesk-pipeline',
		'fdp_render_pipeline'
	);
	add_submenu_page(
	'flowdesk-pro',
	'Settings',
	'Settings',
	'manage_options',
	'flowdesk-settings',
	'fdp_render_settings_page'
);
}

add_action(
	'admin_menu',
	'fdp_register_admin_menu'
);


/**
 * Load FlowDesk Pro admin CSS.
 */
function fdp_admin_assets() {

	$page = isset( $_GET['page'] )
		? sanitize_key(
			wp_unslash(
				$_GET['page']
			)
		)
		: '';

	$screen = get_current_screen();


	$is_flowdesk_page = in_array(
		$page,
	array(
	'flowdesk-pro',
	'flowdesk-pipeline',
	'flowdesk-settings',
),
		true
	);


	$is_lead_page = (
		$screen &&
		'fdp_lead' === $screen->post_type
	);


	if ( ! $is_flowdesk_page && ! $is_lead_page ) {
		return;
	}


	/**
	 * Load FlowDesk admin CSS.
	 */
	wp_enqueue_style(
		'fdp-admin-style',
		FDP_URL . 'assets/css/admin-style.css',
		array(),
		FDP_VERSION
	);


	/**
	 * Only load Kanban JavaScript
	 * on Pipeline page.
	 */
	if ( 'flowdesk-pipeline' === $page ) {

		wp_enqueue_script(
			'fdp-kanban',
			FDP_URL . 'assets/js/kanban.js',
			array(),
			FDP_VERSION,
			true
		);


		wp_localize_script(
			'fdp-kanban',
			'fdpPipeline',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),

				'nonce' => wp_create_nonce(
					'fdp_pipeline_nonce'
				),
			)
		);
		
	}
}
add_action(
	'admin_enqueue_scripts',
	'fdp_admin_assets'
);
/**
 * Render Dashboard.
 */
function fdp_render_dashboard() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	require FDP_PATH . 'admin/dashboard-page.php';
}


/**
 * Render Pipeline.
 */
function fdp_render_pipeline() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	require FDP_PATH . 'admin/pipeline-page.php';
}