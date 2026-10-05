<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* =========================================================
 * KANBAN - UPDATE LEAD STATUS
 * =========================================================
 */

function fdp_ajax_update_lead_status() {

	check_ajax_referer(
		'fdp_pipeline_nonce',
		'nonce'
	);


	$lead_id = isset( $_POST['lead_id'] )
		? absint( $_POST['lead_id'] )
		: 0;


	$status = isset( $_POST['status'] )
		? sanitize_key(
			wp_unslash(
				$_POST['status']
			)
		)
		: '';


	if ( ! $lead_id ) {

		wp_send_json_error(
			array(
				'message' => 'Invalid lead.',
			)
		);
	}


	if ( 'fdp_lead' !== get_post_type( $lead_id ) ) {

		wp_send_json_error(
			array(
				'message' => 'Invalid lead type.',
			)
		);
	}


	if ( ! current_user_can( 'edit_post', $lead_id ) ) {

		wp_send_json_error(
			array(
				'message' => 'You cannot edit this lead.',
			)
		);
	}


	$allowed_statuses = array(
		'new',
		'contacted',
		'qualified',
		'won',
		'lost',
	);


	if ( ! in_array( $status, $allowed_statuses, true ) ) {

		wp_send_json_error(
			array(
				'message' => 'Invalid status.',
			)
		);
	}

$old_status = get_post_meta(
	$lead_id,
	'_fdp_status',
	true
);

if ( empty( $old_status ) ) {
	$old_status = 'new';
}


update_post_meta(
	$lead_id,
	'_fdp_status',
	$status
);


if ( $old_status !== $status ) {

	$status_labels = array(
		'new'       => 'New',
		'contacted' => 'Contacted',
		'qualified' => 'Qualified',
		'won'       => 'Won',
		'lost'      => 'Lost',
	);


	$old_label = isset( $status_labels[ $old_status ] )
		? $status_labels[ $old_status ]
		: ucfirst( $old_status );


	$new_label = isset( $status_labels[ $status ] )
		? $status_labels[ $status ]
		: ucfirst( $status );


	fdp_add_activity(
		$lead_id,
		'Status changed',
		$old_label . ' → ' . $new_label
	);
}


	wp_send_json_success(
		array(
			'message' => 'Lead status updated.',
			'status'  => $status,
		)
	);
}


add_action(
	'wp_ajax_fdp_update_lead_status',
	'fdp_ajax_update_lead_status'
);


/* =========================================================
 * EMAIL NOTIFICATION
 * =========================================================
 */

function fdp_send_new_lead_email(
	$lead_id,
	$lead_data,
	$status,
	$recipient
) {

	if ( ! is_email( $recipient ) ) {
		return false;
	}


	$site_name = wp_specialchars_decode(
		get_bloginfo( 'name' ),
		ENT_QUOTES
	);


	$subject = sprintf(
		'[%s] New Lead: %s',
		$site_name,
		$lead_data['name']
	);


	$edit_url = get_edit_post_link(
		$lead_id,
		''
	);


	$status_labels = array(
		'new'       => 'New',
		'contacted' => 'Contacted',
		'qualified' => 'Qualified',
		'won'       => 'Won',
		'lost'      => 'Lost',
	);


	$status_label = isset( $status_labels[ $status ] )
		? $status_labels[ $status ]
		: 'New';


	$message  = "A new lead has been submitted through your website.\n\n";

	$message .= "Name: " . $lead_data['name'] . "\n";

	$message .= "Email: " . $lead_data['email'] . "\n";


	$message .= "Phone: ";

	$message .= ! empty( $lead_data['phone'] )
		? $lead_data['phone']
		: 'Not provided';

	$message .= "\n";


	$message .= "Company: ";

	$message .= ! empty( $lead_data['company'] )
		? $lead_data['company']
		: 'Not provided';

	$message .= "\n";


	$message .= "Budget: ";

	$message .= ! empty( $lead_data['budget'] )
		? $lead_data['budget']
		: 'Not provided';

	$message .= "\n\n";


	$message .= "Status: " . $status_label . "\n";

	$message .= "Source: Website\n\n";


	if ( $edit_url ) {

		$message .= "View Lead:\n";

		$message .= $edit_url . "\n";
	}


	$headers = array();


	if ( is_email( $lead_data['email'] ) ) {

		$headers[] = sprintf(
			'Reply-To: %s <%s>',
			$lead_data['name'],
			$lead_data['email']
		);
	}


	return wp_mail(
		$recipient,
		$subject,
		$message,
		$headers
	);
}


/* =========================================================
 * FRONTEND - SUBMIT NEW LEAD
 * =========================================================
 */

function fdp_ajax_submit_lead() {
	/**
 * Honeypot spam protection.
 */
$honeypot = isset( $_POST['fdp_website'] )
	? sanitize_text_field(
		wp_unslash(
			$_POST['fdp_website']
		)
	)
	: '';

if ( ! empty( $honeypot ) ) {

	wp_send_json_success(
		array(
			'message' => 'Thank you! Your enquiry has been submitted successfully.',
		)
	);
}


/**
 * Basic submission rate limiting.
 */
$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
	? sanitize_text_field(
		wp_unslash(
			$_SERVER['REMOTE_ADDR']
		)
	)
	: 'unknown';


$rate_key = 'fdp_rate_' . hash_hmac(
	'sha256',
	$remote_ip,
	wp_salt( 'nonce' )
);


$submission_count = (int) get_transient(
	$rate_key
);


if ( $submission_count >= 10 ) {

	wp_send_json_error(
		array(
			'message' => 'Too many submissions. Please try again later.',
		)
	);
}


set_transient(
	$rate_key,
	$submission_count + 1,
	10 * MINUTE_IN_SECONDS
);

	/**
	 * Security check.
	 */
	check_ajax_referer(
		'fdp_submit_lead',
		'fdp_form_nonce'
	);


	/**
	 * Get FlowDesk settings.
	 */
	$settings = get_option(
		'fdp_settings',
		array()
	);


	/**
	 * Allowed statuses.
	 */
	$allowed_statuses = array(
		'new',
		'contacted',
		'qualified',
		'won',
		'lost',
	);


	/**
	 * Default Lead Status from Settings.
	 */
	$default_status = isset( $settings['default_status'] )
		? sanitize_key( $settings['default_status'] )
		: 'new';


	if (
		! in_array(
			$default_status,
			$allowed_statuses,
			true
		)
	) {
		$default_status = 'new';
	}


	/**
	 * Email notification setting.
	 */
	$email_notifications = isset( $settings['email_notifications'] )
		? (int) $settings['email_notifications']
		: 1;


	/**
	 * Notification recipient.
	 */
	$notification_email = isset( $settings['notification_email'] )
		? sanitize_email(
			$settings['notification_email']
		)
		: '';


	if ( ! is_email( $notification_email ) ) {

		$notification_email = get_option(
			'admin_email'
		);
	}


	/**
	 * Get and sanitize submitted data.
	 */
	$name = isset( $_POST['fdp_name'] )
		? sanitize_text_field(
			wp_unslash(
				$_POST['fdp_name']
			)
		)
		: '';


	$email = isset( $_POST['fdp_email'] )
		? sanitize_email(
			wp_unslash(
				$_POST['fdp_email']
			)
		)
		: '';


	$phone = isset( $_POST['fdp_phone'] )
		? sanitize_text_field(
			wp_unslash(
				$_POST['fdp_phone']
			)
		)
		: '';


	$company = isset( $_POST['fdp_company'] )
		? sanitize_text_field(
			wp_unslash(
				$_POST['fdp_company']
			)
		)
		: '';


	$budget = isset( $_POST['fdp_budget'] )
		? sanitize_text_field(
			wp_unslash(
				$_POST['fdp_budget']
			)
		)
		: '';


	/**
	 * Validate Name.
	 */
	if ( empty( $name ) ) {

		wp_send_json_error(
			array(
				'message' => 'Please enter your name.',
			)
		);
	}


	/**
	 * Validate Email.
	 */
	if (
		empty( $email ) ||
		! is_email( $email )
	) {

		wp_send_json_error(
			array(
				'message' => 'Please enter a valid email address.',
			)
		);
	}


	/**
	 * Create Lead.
	 */
	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'fdp_lead',
			'post_status' => 'publish',
			'post_title'  => $name,
		),
		true
	);


	if ( is_wp_error( $lead_id ) ) {

		wp_send_json_error(
			array(
				'message' => 'Unable to create your enquiry. Please try again.',
			)
		);
	}


	/**
	 * Save Lead information.
	 */
	update_post_meta(
		$lead_id,
		'_fdp_email',
		$email
	);


	update_post_meta(
		$lead_id,
		'_fdp_phone',
		$phone
	);


	update_post_meta(
		$lead_id,
		'_fdp_company',
		$company
	);


	update_post_meta(
		$lead_id,
		'_fdp_budget',
		$budget
	);


	update_post_meta(
		$lead_id,
		'_fdp_source',
		'website'
	);


	update_post_meta(
		$lead_id,
		'_fdp_status',
		$default_status
	);

	/**
 * Record Lead creation.
 */
fdp_add_activity(
	$lead_id,
	'Lead created',
	'New enquiry received from the website form.'
);

	/**
	 * Send notification only if enabled.
	 */
	if ( 1 === $email_notifications ) {

		fdp_send_new_lead_email(
			$lead_id,
			array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => $phone,
				'company' => $company,
				'budget'  => $budget,
			),
			$default_status,
			$notification_email
		);
	}


	/**
	 * Success response.
	 */
	wp_send_json_success(
		array(
			'message' => 'Thank you! Your enquiry has been submitted successfully.',
		)
	);
}


/**
 * Logged-in users.
 */
add_action(
	'wp_ajax_fdp_submit_lead',
	'fdp_ajax_submit_lead'
);


/**
 * Logged-out visitors.
 */
add_action(
	'wp_ajax_nopriv_fdp_submit_lead',
	'fdp_ajax_submit_lead'
);