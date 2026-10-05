<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Internal Notes meta box.
 */
function fdp_add_lead_notes_meta_box() {

	add_meta_box(
		'fdp_lead_notes',
		'Internal Notes',
		'fdp_render_lead_notes_meta_box',
		'fdp_lead',
		'normal',
		'default'
	);
}

add_action(
	'add_meta_boxes',
	'fdp_add_lead_notes_meta_box'
);


/**
 * Render Internal Notes box.
 */
function fdp_render_lead_notes_meta_box( $post ) {

	wp_nonce_field(
		'fdp_save_lead_note',
		'fdp_note_nonce'
	);

	$notes = get_post_meta(
		$post->ID,
		'_fdp_notes',
		true
	);

	if ( ! is_array( $notes ) ) {
		$notes = array();
	}

	?>

	<div class="fdp-notes-box">

		<div class="fdp-add-note">

			<label for="fdp_new_note">
				Add New Note
			</label>

			<textarea
				id="fdp_new_note"
				name="fdp_new_note"
				rows="4"
				placeholder="Write a private note about this lead..."
			></textarea>

			<p class="description">
				This note is private and only visible inside WordPress admin.
			</p>

		</div>


		<?php if ( ! empty( $notes ) ) : ?>

			<div class="fdp-notes-history">

				<h3>Notes History</h3>

				<?php
				$notes = array_reverse( $notes );
				?>

				<?php foreach ( $notes as $note ) : ?>

					<?php

					$user = get_userdata(
						isset( $note['user_id'] )
							? absint( $note['user_id'] )
							: 0
					);

					$author_name = $user
						? $user->display_name
						: 'Unknown User';

					$timestamp = isset( $note['timestamp'] )
						? absint( $note['timestamp'] )
						: 0;

					?>

					<div class="fdp-note-item">

						<div class="fdp-note-meta">

							<strong>
								<?php echo esc_html( $author_name ); ?>
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

						<div class="fdp-note-text">
							<?php echo nl2br( esc_html( $note['text'] ) ); ?>
						</div>

					</div>

				<?php endforeach; ?>

			</div>

		<?php else : ?>

			<div class="fdp-no-notes">
				No notes added yet.
			</div>

		<?php endif; ?>

	</div>

	<?php
}


/**
 * Save new Internal Note.
 */
function fdp_save_lead_note( $post_id ) {

	if (
		! isset( $_POST['fdp_note_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['fdp_note_nonce'] )
			),
			'fdp_save_lead_note'
		)
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'fdp_lead' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( empty( $_POST['fdp_new_note'] ) ) {
		return;
	}

	$new_note = sanitize_textarea_field(
		wp_unslash( $_POST['fdp_new_note'] )
	);

	if ( empty( $new_note ) ) {
		return;
	}

	$notes = get_post_meta(
		$post_id,
		'_fdp_notes',
		true
	);

	if ( ! is_array( $notes ) ) {
		$notes = array();
	}

	$notes[] = array(
		'text'      => $new_note,
		'user_id'   => get_current_user_id(),
		'timestamp' => current_time( 'timestamp' ),
	);

	update_post_meta(
		$post_id,
		'_fdp_notes',
		$notes
	);

	fdp_add_activity(
	$post_id,
	'Internal note added',
	'A new private note was added to this lead.'
);
}

add_action(
	'save_post_fdp_lead',
	'fdp_save_lead_note'
);