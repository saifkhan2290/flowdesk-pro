<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Make CSV values safer for spreadsheet applications.
 */
function fdp_csv_safe_value( $value ) {

	$value = (string) $value;

	if (
		$value !== '' &&
		in_array( $value[0], array( '=', '+', '-', '@' ), true )
	) {
		$value = "'" . $value;
	}

	return $value;
}


/**
 * Add Export CSV button to All Leads screen.
 */
function fdp_add_export_button() {

	$screen = get_current_screen();

	if (
		! $screen ||
		'fdp_lead' !== $screen->post_type
	) {
		return;
	}


	$export_url = wp_nonce_url(
		admin_url(
			'admin-post.php?action=fdp_export_leads'
		),
		'fdp_export_leads'
	);

	?>

	<a
		href="<?php echo esc_url( $export_url ); ?>"
		class="button"
	>
		Export CSV
	</a>

	<?php
}

add_action(
	'restrict_manage_posts',
	'fdp_add_export_button',
	20
);


/**
 * Export all FlowDesk Leads as CSV.
 */
function fdp_export_leads_csv() {

	/**
	 * Permission check.
	 */
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed to export leads.' );
	}


	/**
	 * Security check.
	 */
	check_admin_referer(
		'fdp_export_leads'
	);


	/**
	 * Get all Leads.
	 */
	$leads = get_posts(
		array(
			'post_type'      => 'fdp_lead',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);


	/**
	 * CSV filename.
	 */
	$filename = 'flowdesk-leads-' . wp_date( 'Y-m-d' ) . '.csv';


	nocache_headers();

	header(
		'Content-Type: text/csv; charset=utf-8'
	);

	header(
		'Content-Disposition: attachment; filename="' . $filename . '"'
	);


	$output = fopen(
		'php://output',
		'w'
	);


	if ( false === $output ) {
		wp_die( 'Unable to create CSV file.' );
	}


	/**
	 * UTF-8 BOM for Excel compatibility.
	 */
	fwrite(
		$output,
		"\xEF\xBB\xBF"
	);


	/**
	 * CSV headings.
	 */
	fputcsv(
		$output,
		array(
			'Lead Name',
			'Email',
			'Phone',
			'Company',
			'Budget',
			'Source',
			'Status',
			'Follow-up Date',
			'Date Added',
		)
	);


	$status_labels = array(
		'new'       => 'New',
		'contacted' => 'Contacted',
		'qualified' => 'Qualified',
		'won'       => 'Won',
		'lost'      => 'Lost',
	);


	$source_labels = array(
		'website'      => 'Website',
		'referral'     => 'Referral',
		'social-media' => 'Social Media',
		'other'        => 'Other',
	);


	/**
	 * Add each Lead to CSV.
	 */
	foreach ( $leads as $lead ) {

		$email = get_post_meta(
			$lead->ID,
			'_fdp_email',
			true
		);


		$phone = get_post_meta(
			$lead->ID,
			'_fdp_phone',
			true
		);


		$company = get_post_meta(
			$lead->ID,
			'_fdp_company',
			true
		);


		$budget = get_post_meta(
			$lead->ID,
			'_fdp_budget',
			true
		);


		$source = get_post_meta(
			$lead->ID,
			'_fdp_source',
			true
		);


		$status = get_post_meta(
			$lead->ID,
			'_fdp_status',
			true
		);


		$followup_date = get_post_meta(
			$lead->ID,
			'_fdp_followup_date',
			true
		);


		if ( empty( $status ) ) {
			$status = 'new';
		}


		$status_label = isset( $status_labels[ $status ] )
			? $status_labels[ $status ]
			: ucfirst( $status );


		$source_label = isset( $source_labels[ $source ] )
			? $source_labels[ $source ]
			: ucfirst( $source );


		fputcsv(
			$output,
			array(
				fdp_csv_safe_value( $lead->post_title ),
				fdp_csv_safe_value( $email ),
				fdp_csv_safe_value( $phone ),
				fdp_csv_safe_value( $company ),
				fdp_csv_safe_value( $budget ),
				fdp_csv_safe_value( $source_label ),
				fdp_csv_safe_value( $status_label ),
				fdp_csv_safe_value( $followup_date ),
				fdp_csv_safe_value(
					get_the_date(
						'Y-m-d H:i:s',
						$lead
					)
				),
			)
		);
	}


	fclose( $output );

	exit;
}


add_action(
	'admin_post_fdp_export_leads',
	'fdp_export_leads_csv'
);