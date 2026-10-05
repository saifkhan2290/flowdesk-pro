<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add a new activity entry to a Lead.
 */
function fdp_add_activity( $lead_id, $action, $details = '' ) {

	if ( 'fdp_lead' !== get_post_type( $lead_id ) ) {
		return;
	}

	$activity_log = get_post_meta(
		$lead_id,
		'_fdp_activity_log',
		true
	);

	if ( ! is_array( $activity_log ) ) {
		$activity_log = array();
	}


	$activity_log[] = array(
		'action'    => sanitize_text_field( $action ),
		'details'   => sanitize_text_field( $details ),
		'user_id'   => get_current_user_id(),
		'timestamp' => time(),
	);


	/**
	 * Keep latest 100 activities only.
	 */
	if ( count( $activity_log ) > 100 ) {

		$activity_log = array_slice(
			$activity_log,
			-100
		);
	}


	update_post_meta(
		$lead_id,
		'_fdp_activity_log',
		$activity_log
	);
}


/**
 * Register Activity Log meta box.
 */
function fdp_add_activity_meta_box() {

	add_meta_box(
		'fdp_activity_log',
		'Activity Log',
		'fdp_render_activity_log',
		'fdp_lead',
		'normal',
		'default'
	);
}

add_action(
	'add_meta_boxes',
	'fdp_add_activity_meta_box'
);


/**
 * Render Activity Log.
 */
function fdp_render_activity_log( $post ) {

	$activity_log = get_post_meta(
		$post->ID,
		'_fdp_activity_log',
		true
	);


	if ( ! is_array( $activity_log ) ) {
		$activity_log = array();
	}


	if ( empty( $activity_log ) ) {

		?>

		<div class="fdp-activity-empty">
			No activity recorded yet.
		</div>

		<?php

		return;
	}


	/**
	 * Newest activity first.
	 */
	$activity_log = array_reverse(
		$activity_log
	);

	?>

	<div class="fdp-activity-list">

		<?php foreach ( $activity_log as $activity ) : ?>

			<?php

			$user_id = isset( $activity['user_id'] )
				? absint( $activity['user_id'] )
				: 0;


			$user = $user_id
				? get_userdata( $user_id )
				: false;


			$actor_name = $user
				? $user->display_name
				: 'System';


			$action = isset( $activity['action'] )
				? $activity['action']
				: 'Activity';


			$details = isset( $activity['details'] )
				? $activity['details']
				: '';


			$timestamp = isset( $activity['timestamp'] )
				? absint( $activity['timestamp'] )
				: 0;

			?>

			<div class="fdp-activity-item">

				<div class="fdp-activity-marker"></div>


				<div class="fdp-activity-content">

					<div class="fdp-activity-top">

						<strong>
							<?php echo esc_html( $action ); ?>
						</strong>

						<span>
							<?php
							echo esc_html(
								wp_date(
									'M j, Y - g:i a',
									$timestamp
								)
							);
							?>
						</span>

					</div>


					<?php if ( $details ) : ?>

						<p>
							<?php echo esc_html( $details ); ?>
						</p>

					<?php endif; ?>


					<small>
						By <?php echo esc_html( $actor_name ); ?>
					</small>

				</div>

			</div>

		<?php endforeach; ?>

	</div>

	<?php
}