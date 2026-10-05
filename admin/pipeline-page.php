<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Pipeline statuses.
 */
$pipeline_statuses = array(
	'new'       => 'New',
	'contacted' => 'Contacted',
	'qualified' => 'Qualified',
	'won'       => 'Won',
	'lost'      => 'Lost',
);

?>

<div class="fdp-admin">

	<div class="fdp-header">

		<div>

			<p class="fdp-eyebrow">
				SALES PIPELINE
			</p>

			<h1>Lead Pipeline</h1>

			<p class="fdp-subtitle">
				Drag leads between stages to update their status.
			</p>

		</div>

	</div>


	<div class="fdp-kanban-board">

		<?php foreach ( $pipeline_statuses as $status_key => $status_label ) : ?>


			<?php

			if ( 'new' === $status_key ) {

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

			} else {

				$meta_query = array(
					array(
						'key'   => '_fdp_status',
						'value' => $status_key,
					),
				);
			}


			$lead_query = new WP_Query(
				array(
					'post_type'      => 'fdp_lead',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'meta_query'     => $meta_query,
				)
			);

			?>


			<div class="fdp-kanban-column">


				<div class="fdp-kanban-header">

					<div class="fdp-kanban-title">

						<span
							class="fdp-kanban-dot fdp-kanban-<?php echo esc_attr( $status_key ); ?>"
						></span>

						<h2>
							<?php echo esc_html( $status_label ); ?>
						</h2>

					</div>


					<span class="fdp-kanban-count">

						<?php echo esc_html( $lead_query->found_posts ); ?>

					</span>

				</div>


				<div
					class="fdp-kanban-cards"
					data-status="<?php echo esc_attr( $status_key ); ?>"
				>


					<?php if ( $lead_query->have_posts() ) : ?>


						<?php while ( $lead_query->have_posts() ) : ?>


							<?php

							$lead_query->the_post();

							$lead_id = get_the_ID();


							$company = get_post_meta(
								$lead_id,
								'_fdp_company',
								true
							);


							$budget = get_post_meta(
								$lead_id,
								'_fdp_budget',
								true
							);


							$followup_date = get_post_meta(
								$lead_id,
								'_fdp_followup_date',
								true
							);

							?>


							<div
								class="fdp-kanban-card"
								draggable="true"
								data-lead-id="<?php echo esc_attr( $lead_id ); ?>"
							>


								<div class="fdp-kanban-card-top">

									<div class="fdp-kanban-avatar">

										<?php
										echo esc_html(
											strtoupper(
												substr(
													get_the_title(),
													0,
													1
												)
											)
										);
										?>

									</div>


									<div class="fdp-kanban-card-name">

										<strong>
											<?php echo esc_html( get_the_title() ); ?>
										</strong>


										<?php if ( $company ) : ?>

											<span>
												<?php echo esc_html( $company ); ?>
											</span>

										<?php endif; ?>

									</div>

								</div>


								<?php if ( $budget ) : ?>

									<div class="fdp-kanban-detail">

										<span>Budget</span>

										<strong>
											<?php echo esc_html( $budget ); ?>
										</strong>

									</div>

								<?php endif; ?>


								<?php if ( $followup_date ) : ?>

									<div class="fdp-kanban-detail">

										<span>
											Follow-up
										</span>

										<strong>

											<?php
											echo esc_html(
												wp_date(
													'M j, Y',
													strtotime( $followup_date )
												)
											);
											?>

										</strong>

									</div>

								<?php endif; ?>


								<div class="fdp-kanban-card-actions">

									<a
										class="fdp-kanban-edit"
										href="<?php echo esc_url( get_edit_post_link( $lead_id ) ); ?>"
										draggable="false"
									>
										Edit Lead
									</a>

								</div>

							</div>


						<?php endwhile; ?>


						<?php wp_reset_postdata(); ?>


					<?php else : ?>


						<div class="fdp-kanban-empty">
							No leads
						</div>


					<?php endif; ?>


				</div>

			</div>


		<?php endforeach; ?>

	</div>

</div>