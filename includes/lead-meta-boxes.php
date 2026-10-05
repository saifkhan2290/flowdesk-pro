<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Lead Details meta box.
 */
function fdp_add_lead_meta_boxes() {

	add_meta_box(
		'fdp_lead_details',
		'Lead Details',
		'fdp_render_lead_details_meta_box',
		'fdp_lead',
		'normal',
		'high'
	);
}

add_action(
	'add_meta_boxes',
	'fdp_add_lead_meta_boxes'
);


/**
 * Render Lead Details fields.
 */
function fdp_render_lead_details_meta_box( $post ) {

	wp_nonce_field(
		'fdp_save_lead_details',
		'fdp_lead_nonce'
	);


	$email = get_post_meta(
		$post->ID,
		'_fdp_email',
		true
	);


	$phone = get_post_meta(
		$post->ID,
		'_fdp_phone',
		true
	);


	$company = get_post_meta(
		$post->ID,
		'_fdp_company',
		true
	);


	$budget = get_post_meta(
		$post->ID,
		'_fdp_budget',
		true
	);


	$source = get_post_meta(
		$post->ID,
		'_fdp_source',
		true
	);


	$status = get_post_meta(
		$post->ID,
		'_fdp_status',
		true
	);


	$followup_date = get_post_meta(
		$post->ID,
		'_fdp_followup_date',
		true
	);


	if ( empty( $status ) ) {
		$status = 'new';
	}

	?>

	<table class="form-table">

		<tr>

			<th>
				<label for="fdp_email">
					Email
				</label>
			</th>

			<td>

				<input
					type="email"
					id="fdp_email"
					name="fdp_email"
					class="regular-text"
					value="<?php echo esc_attr( $email ); ?>"
				>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_phone">
					Phone
				</label>
			</th>

			<td>

				<input
					type="text"
					id="fdp_phone"
					name="fdp_phone"
					class="regular-text"
					value="<?php echo esc_attr( $phone ); ?>"
				>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_company">
					Company
				</label>
			</th>

			<td>

				<input
					type="text"
					id="fdp_company"
					name="fdp_company"
					class="regular-text"
					value="<?php echo esc_attr( $company ); ?>"
				>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_budget">
					Budget
				</label>
			</th>

			<td>

				<input
					type="text"
					id="fdp_budget"
					name="fdp_budget"
					class="regular-text"
					placeholder="Example: $1500"
					value="<?php echo esc_attr( $budget ); ?>"
				>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_source">
					Lead Source
				</label>
			</th>

			<td>

				<select
					id="fdp_source"
					name="fdp_source"
				>

					<option value="">
						Select Source
					</option>

					<option
						value="website"
						<?php selected( $source, 'website' ); ?>
					>
						Website
					</option>

					<option
						value="referral"
						<?php selected( $source, 'referral' ); ?>
					>
						Referral
					</option>

					<option
						value="social-media"
						<?php selected( $source, 'social-media' ); ?>
					>
						Social Media
					</option>

					<option
						value="other"
						<?php selected( $source, 'other' ); ?>
					>
						Other
					</option>

				</select>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_status">
					Lead Status
				</label>
			</th>

			<td>

				<select
					id="fdp_status"
					name="fdp_status"
				>

					<option
						value="new"
						<?php selected( $status, 'new' ); ?>
					>
						New
					</option>

					<option
						value="contacted"
						<?php selected( $status, 'contacted' ); ?>
					>
						Contacted
					</option>

					<option
						value="qualified"
						<?php selected( $status, 'qualified' ); ?>
					>
						Qualified
					</option>

					<option
						value="won"
						<?php selected( $status, 'won' ); ?>
					>
						Won
					</option>

					<option
						value="lost"
						<?php selected( $status, 'lost' ); ?>
					>
						Lost
					</option>

				</select>

			</td>

		</tr>


		<tr>

			<th>
				<label for="fdp_followup_date">
					Follow-up Date
				</label>
			</th>

			<td>

				<input
					type="date"
					id="fdp_followup_date"
					name="fdp_followup_date"
					value="<?php echo esc_attr( $followup_date ); ?>"
				>

			</td>

		</tr>

	</table>

	<?php
}


/**
 * Save Lead Details.
 */
function fdp_save_lead_details( $post_id ) {

	/**
	 * Verify FlowDesk nonce.
	 */
	if (
		! isset( $_POST['fdp_lead_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field(
				wp_unslash(
					$_POST['fdp_lead_nonce']
				)
			),
			'fdp_save_lead_details'
		)
	) {
		return;
	}


	/**
	 * Stop autosaves.
	 */
	if (
		defined( 'DOING_AUTOSAVE' ) &&
		DOING_AUTOSAVE
	) {
		return;
	}


	/**
	 * Stop revisions.
	 */
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}


	/**
	 * Only save FlowDesk Leads.
	 */
	if ( 'fdp_lead' !== get_post_type( $post_id ) ) {
		return;
	}


	/**
	 * Permission check.
	 */
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}


	/**
	 * Email.
	 */
	if ( isset( $_POST['fdp_email'] ) ) {

		$email = sanitize_email(
			wp_unslash(
				$_POST['fdp_email']
			)
		);

		update_post_meta(
			$post_id,
			'_fdp_email',
			$email
		);
	}


	/**
	 * Phone.
	 */
	if ( isset( $_POST['fdp_phone'] ) ) {

		$phone = sanitize_text_field(
			wp_unslash(
				$_POST['fdp_phone']
			)
		);

		update_post_meta(
			$post_id,
			'_fdp_phone',
			$phone
		);
	}


	/**
	 * Company.
	 */
	if ( isset( $_POST['fdp_company'] ) ) {

		$company = sanitize_text_field(
			wp_unslash(
				$_POST['fdp_company']
			)
		);

		update_post_meta(
			$post_id,
			'_fdp_company',
			$company
		);
	}


	/**
	 * Budget.
	 */
	if ( isset( $_POST['fdp_budget'] ) ) {

		$budget = sanitize_text_field(
			wp_unslash(
				$_POST['fdp_budget']
			)
		);

		update_post_meta(
			$post_id,
			'_fdp_budget',
			$budget
		);
	}


	/**
	 * Lead Source.
	 */
	if ( isset( $_POST['fdp_source'] ) ) {

		$source = sanitize_key(
			wp_unslash(
				$_POST['fdp_source']
			)
		);


		$allowed_sources = array(
			'website',
			'referral',
			'social-media',
			'other',
		);


		if ( empty( $source ) ) {

			delete_post_meta(
				$post_id,
				'_fdp_source'
			);

		} elseif (
			in_array(
				$source,
				$allowed_sources,
				true
			)
		) {

			update_post_meta(
				$post_id,
				'_fdp_source',
				$source
			);
		}
	}


	/**
	 * Follow-up Date.
	 */
	if ( isset( $_POST['fdp_followup_date'] ) ) {

		$followup_date = sanitize_text_field(
			wp_unslash(
				$_POST['fdp_followup_date']
			)
		);


		if ( empty( $followup_date ) ) {

			delete_post_meta(
				$post_id,
				'_fdp_followup_date'
			);

		} elseif (
			preg_match(
				'/^\d{4}-\d{2}-\d{2}$/',
				$followup_date
			)
		) {

			$date_parts = array_map(
				'intval',
				explode(
					'-',
					$followup_date
				)
			);


			if (
				3 === count( $date_parts ) &&
				checkdate(
					$date_parts[1],
					$date_parts[2],
					$date_parts[0]
				)
			) {

				update_post_meta(
					$post_id,
					'_fdp_followup_date',
					$followup_date
				);
			}
		}
	}


	/**
	 * Lead Status.
	 */
	if ( isset( $_POST['fdp_status'] ) ) {

		$status = sanitize_key(
			wp_unslash(
				$_POST['fdp_status']
			)
		);


		$allowed_statuses = array(
			'new',
			'contacted',
			'qualified',
			'won',
			'lost',
		);


		if (
			in_array(
				$status,
				$allowed_statuses,
				true
			)
		) {

			$old_status = get_post_meta(
				$post_id,
				'_fdp_status',
				true
			);


			if ( empty( $old_status ) ) {
				$old_status = 'new';
			}


			update_post_meta(
				$post_id,
				'_fdp_status',
				$status
			);


			/**
			 * Record status change.
			 */
			if ( $old_status !== $status ) {

				$status_labels = array(
					'new'       => 'New',
					'contacted' => 'Contacted',
					'qualified' => 'Qualified',
					'won'       => 'Won',
					'lost'      => 'Lost',
				);


				$old_label = isset(
					$status_labels[ $old_status ]
				)
					? $status_labels[ $old_status ]
					: ucfirst( $old_status );


				$new_label = isset(
					$status_labels[ $status ]
				)
					? $status_labels[ $status ]
					: ucfirst( $status );


				if ( function_exists( 'fdp_add_activity' ) ) {

					fdp_add_activity(
						$post_id,
						'Status changed',
						$old_label . ' → ' . $new_label
					);
				}
			}
		}
	}
}


add_action(
	'save_post_fdp_lead',
	'fdp_save_lead_details'
);