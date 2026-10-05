<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customize Lead list columns.
 */
function fdp_lead_columns( $columns ) {

	$new_columns = array();

	$new_columns['cb']         = $columns['cb'];
	$new_columns['title']      = 'Lead Name';
	$new_columns['fdp_email']  = 'Email';
	$new_columns['fdp_company'] = 'Company';
	$new_columns['fdp_budget'] = 'Budget';
	$new_columns['fdp_source'] = 'Source';
	$new_columns['fdp_status'] = 'Status';
    $new_columns['fdp_followup'] = 'Follow-up';
	$new_columns['date']       = 'Date';

	return $new_columns;
}

add_filter(
	'manage_fdp_lead_posts_columns',
	'fdp_lead_columns'
);


/**
 * Display Lead data inside columns.
 */
function fdp_lead_column_content( $column, $post_id ) {
    if ( 'fdp_followup' === $column ) {

	$followup_date = get_post_meta(
		$post_id,
		'_fdp_followup_date',
		true
	);

	if ( $followup_date ) {

		echo esc_html(
			wp_date(
				'M j, Y',
				strtotime( $followup_date )
			)
		);

	} else {

		echo '—';
	}
}

	if ( 'fdp_email' === $column ) {

		$email = get_post_meta(
			$post_id,
			'_fdp_email',
			true
		);

		echo esc_html( $email ? $email : '—' );
	}


	if ( 'fdp_company' === $column ) {

		$company = get_post_meta(
			$post_id,
			'_fdp_company',
			true
		);

		echo esc_html( $company ? $company : '—' );
	}


	if ( 'fdp_budget' === $column ) {

		$budget = get_post_meta(
			$post_id,
			'_fdp_budget',
			true
		);

		echo esc_html( $budget ? $budget : '—' );
	}


	if ( 'fdp_source' === $column ) {

		$source = get_post_meta(
			$post_id,
			'_fdp_source',
			true
		);

		if ( $source ) {

			echo esc_html(
				ucwords(
					str_replace(
						'-',
						' ',
						$source
					)
				)
			);

		} else {

			echo '—';
		}
	}


	if ( 'fdp_status' === $column ) {

		$status = get_post_meta(
			$post_id,
			'_fdp_status',
			true
		);

		if ( ! $status ) {
			$status = 'new';
		}

		$allowed_statuses = array(
			'new'       => 'New',
			'contacted' => 'Contacted',
			'qualified' => 'Qualified',
			'won'       => 'Won',
			'lost'      => 'Lost',
		);

		$label = isset( $allowed_statuses[ $status ] )
			? $allowed_statuses[ $status ]
			: 'New';

		?>

		<span class="fdp-admin-status fdp-status-<?php echo esc_attr( $status ); ?>">
			<?php echo esc_html( $label ); ?>
		</span>

		<?php
	}
}

add_action(
	'manage_fdp_lead_posts_custom_column',
	'fdp_lead_column_content',
	10,
	2
);
/**
 * Add Status and Source filters
 * to the All Leads admin screen.
 */
function fdp_add_lead_admin_filters() {

	$screen = get_current_screen();

	if ( ! $screen || 'fdp_lead' !== $screen->post_type ) {
		return;
	}


	$current_status = isset( $_GET['fdp_status_filter'] )
		? sanitize_key(
			wp_unslash( $_GET['fdp_status_filter'] )
		)
		: '';


	$current_source = isset( $_GET['fdp_source_filter'] )
		? sanitize_key(
			wp_unslash( $_GET['fdp_source_filter'] )
		)
		: '';

	?>

	<select name="fdp_status_filter">

		<option value="">
			All Statuses
		</option>

		<option
			value="new"
			<?php selected( $current_status, 'new' ); ?>
		>
			New
		</option>

		<option
			value="contacted"
			<?php selected( $current_status, 'contacted' ); ?>
		>
			Contacted
		</option>

		<option
			value="qualified"
			<?php selected( $current_status, 'qualified' ); ?>
		>
			Qualified
		</option>

		<option
			value="won"
			<?php selected( $current_status, 'won' ); ?>
		>
			Won
		</option>

		<option
			value="lost"
			<?php selected( $current_status, 'lost' ); ?>
		>
			Lost
		</option>

	</select>


	<select name="fdp_source_filter">

		<option value="">
			All Sources
		</option>

		<option
			value="website"
			<?php selected( $current_source, 'website' ); ?>
		>
			Website
		</option>

		<option
			value="referral"
			<?php selected( $current_source, 'referral' ); ?>
		>
			Referral
		</option>

		<option
			value="social-media"
			<?php selected( $current_source, 'social-media' ); ?>
		>
			Social Media
		</option>

		<option
			value="other"
			<?php selected( $current_source, 'other' ); ?>
		>
			Other
		</option>

	</select>

	<?php
}

add_action(
	'restrict_manage_posts',
	'fdp_add_lead_admin_filters'
);


/**
 * Apply Status and Source filters.
 */
function fdp_filter_lead_admin_query( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}


	$post_type = $query->get( 'post_type' );

	if ( 'fdp_lead' !== $post_type ) {
		return;
	}


	$meta_query = array();


	/**
	 * Status filter.
	 */
	if ( isset( $_GET['fdp_status_filter'] ) ) {

		$status = sanitize_key(
			wp_unslash(
				$_GET['fdp_status_filter']
			)
		);

		$allowed_statuses = array(
			'new',
			'contacted',
			'qualified',
			'won',
			'lost',
		);


		if ( in_array( $status, $allowed_statuses, true ) ) {

			if ( 'new' === $status ) {

				$meta_query[] = array(
					'relation' => 'OR',

					array(
						'key'   => '_fdp_status',
						'value' => 'new',
					),

					array(
						'key'     => '_fdp_status',
						'compare' => 'NOT EXISTS',
					),

					array(
						'key'   => '_fdp_status',
						'value' => '',
					),
				);

			} else {

				$meta_query[] = array(
					'key'   => '_fdp_status',
					'value' => $status,
				);
			}
		}
	}


	/**
	 * Source filter.
	 */
	if ( isset( $_GET['fdp_source_filter'] ) ) {

		$source = sanitize_key(
			wp_unslash(
				$_GET['fdp_source_filter']
			)
		);

		$allowed_sources = array(
			'website',
			'referral',
			'social-media',
			'other',
		);


		if ( in_array( $source, $allowed_sources, true ) ) {

			$meta_query[] = array(
				'key'   => '_fdp_source',
				'value' => $source,
			);
		}
	}


	if ( ! empty( $meta_query ) ) {

		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}

		$query->set(
			'meta_query',
			$meta_query
		);
	}
}

add_action(
	'pre_get_posts',
	'fdp_filter_lead_admin_query'
);


/**
 * Extend WordPress Lead search
 * to include Email and Company.
 */
function fdp_extend_lead_admin_search( $search, $query ) {

	global $wpdb;


	if ( ! is_admin() || ! $query->is_main_query() ) {
		return $search;
	}


	if ( 'fdp_lead' !== $query->get( 'post_type' ) ) {
		return $search;
	}


	$search_term = $query->get( 's' );


	if ( empty( $search_term ) ) {
		return $search;
	}


	$like = '%' . $wpdb->esc_like( $search_term ) . '%';


	$search = $wpdb->prepare(
		"
		AND (
			{$wpdb->posts}.post_title LIKE %s

			OR EXISTS (
				SELECT 1
				FROM {$wpdb->postmeta} AS fdp_email_meta
				WHERE fdp_email_meta.post_id = {$wpdb->posts}.ID
				AND fdp_email_meta.meta_key = '_fdp_email'
				AND fdp_email_meta.meta_value LIKE %s
			)

			OR EXISTS (
				SELECT 1
				FROM {$wpdb->postmeta} AS fdp_company_meta
				WHERE fdp_company_meta.post_id = {$wpdb->posts}.ID
				AND fdp_company_meta.meta_key = '_fdp_company'
				AND fdp_company_meta.meta_value LIKE %s
			)
		)
		",
		$like,
		$like,
		$like
	);


	return $search;
}

add_filter(
	'posts_search',
	'fdp_extend_lead_admin_search',
	10,
	2
);