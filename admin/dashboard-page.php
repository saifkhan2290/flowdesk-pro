<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Count leads by status.
 */
function fdp_get_lead_status_count( $status ) {

	$meta_query = array(
		array(
			'key'   => '_fdp_status',
			'value' => $status,
		),
	);

	/**
	 * Old leads without a saved status
	 * are treated as "New".
	 */
	if ( 'new' === $status ) {

		$meta_query = array(
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
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'fdp_lead',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => $meta_query,
		)
	);

	return (int) $query->found_posts;
}


/**
 * Total leads.
 */
$lead_counts = wp_count_posts( 'fdp_lead' );

$total_leads = isset( $lead_counts->publish )
	? (int) $lead_counts->publish
	: 0;


/**
 * Real pipeline counts.
 */
$new_leads       = fdp_get_lead_status_count( 'new' );
$contacted_leads = fdp_get_lead_status_count( 'contacted' );
$qualified_leads = fdp_get_lead_status_count( 'qualified' );
$won_leads       = fdp_get_lead_status_count( 'won' );
$lost_leads      = fdp_get_lead_status_count( 'lost' );
/**
 * Count upcoming follow-ups.
 */
$today = current_time( 'Y-m-d' );

$followup_query = new WP_Query(
	array(
		'post_type'      => 'fdp_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',

		'meta_query' => array(
			array(
				'key'     => '_fdp_followup_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	)
);

$followup_count = (int) $followup_query->found_posts;

/**
 * Latest 5 leads.
 */
$recent_leads = get_posts(
	array(
		'post_type'      => 'fdp_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

?>

<div class="fdp-admin">

	<div class="fdp-header">

		<div>
			<p class="fdp-eyebrow">CLIENT MANAGEMENT</p>

			<h1>FlowDesk Pro</h1>

			<p class="fdp-subtitle">
				Manage your leads, clients and follow-ups from one place.
			</p>
		</div>

		<div class="fdp-status">
			<span></span>
			System Ready
		</div>

	</div>


	<div class="fdp-stats">

		<div class="fdp-stat-card">

			<div class="fdp-stat-icon">👥</div>

			<div>
				<p>Total Leads</p>

				<h2>
					<?php echo esc_html( $total_leads ); ?>
				</h2>
			</div>

		</div>


		<div class="fdp-stat-card">

			<div class="fdp-stat-icon">✨</div>

			<div>
				<p>New Leads</p>

				<h2>
					<?php echo esc_html( $new_leads ); ?>
				</h2>
			</div>

		</div>


		<div class="fdp-stat-card">

			<div class="fdp-stat-icon">✓</div>

			<div>
				<p>Won Leads</p>

				<h2>
					<?php echo esc_html( $won_leads ); ?>
				</h2>
			</div>

		</div>


		<div class="fdp-stat-card">

			<div class="fdp-stat-icon">📅</div>

			<div>
				<p>Follow-ups</p>

<h2>
	<?php echo esc_html( $followup_count ); ?>
</h2>
			</div>

		</div>

	</div>


	<div class="fdp-dashboard-grid">

		<div class="fdp-panel">

			<div class="fdp-panel-header">
				<h2>Recent Leads</h2>
				<p>Your latest client enquiries.</p>
			</div>


			<?php if ( ! empty( $recent_leads ) ) : ?>

				<div class="fdp-lead-list">

					<?php foreach ( $recent_leads as $lead ) : ?>

						<?php

						$status = get_post_meta(
							$lead->ID,
							'_fdp_status',
							true
						);

						if ( ! $status ) {
							$status = 'new';
						}

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

						?>

						<a
							class="fdp-lead-row"
							href="<?php echo esc_url( get_edit_post_link( $lead->ID ) ); ?>"
						>

							<div class="fdp-lead-avatar">

								<?php
								echo esc_html(
									strtoupper(
										substr(
											$lead->post_title,
											0,
											1
										)
									)
								);
								?>

							</div>


							<div class="fdp-lead-info">

								<strong>
									<?php echo esc_html( $lead->post_title ); ?>
								</strong>

								<span>
									Added
									<?php echo esc_html( get_the_date( 'M j, Y', $lead ) ); ?>
								</span>

							</div>


							<span
								class="fdp-admin-status fdp-status-<?php echo esc_attr( $status ); ?>"
							>
								<?php echo esc_html( $status_label ); ?>
							</span>

						</a>

					<?php endforeach; ?>

				</div>

			<?php else : ?>

				<div class="fdp-empty-state">

					<div class="fdp-empty-icon">👤</div>

					<h3>No leads yet</h3>

					<p>
						Your latest leads will appear here once
						FlowDesk starts receiving enquiries.
					</p>

				</div>

			<?php endif; ?>

		</div>


		<div class="fdp-panel">

			<div class="fdp-panel-header">
				<h2>Pipeline Overview</h2>
				<p>Your current lead pipeline.</p>
			</div>


			<div class="fdp-pipeline-item">

				<div>
					<span class="fdp-dot fdp-new"></span>
					New
				</div>

				<strong>
					<?php echo esc_html( $new_leads ); ?>
				</strong>

			</div>


			<div class="fdp-pipeline-item">

				<div>
					<span class="fdp-dot fdp-contacted"></span>
					Contacted
				</div>

				<strong>
					<?php echo esc_html( $contacted_leads ); ?>
				</strong>

			</div>


			<div class="fdp-pipeline-item">

				<div>
					<span class="fdp-dot fdp-qualified"></span>
					Qualified
				</div>

				<strong>
					<?php echo esc_html( $qualified_leads ); ?>
				</strong>

			</div>


			<div class="fdp-pipeline-item">

				<div>
					<span class="fdp-dot fdp-won"></span>
					Won
				</div>

				<strong>
					<?php echo esc_html( $won_leads ); ?>
				</strong>

			</div>


			<div class="fdp-pipeline-item">

				<div>
					<span class="fdp-dot" style="background:#f04438;"></span>
					Lost
				</div>

				<strong>
					<?php echo esc_html( $lost_leads ); ?>
				</strong>

			</div>

		</div>

	</div>

</div>