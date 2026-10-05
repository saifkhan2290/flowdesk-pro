<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * FlowDesk frontend Lead Form.
 */
function fdp_render_lead_form() {

	/**
	 * Get saved FlowDesk settings.
	 */
	$settings = get_option(
		'fdp_settings',
		array()
	);


	$form_title = isset( $settings['form_title'] )
		? $settings['form_title']
		: 'Tell Us About Your Project';


	$form_description = isset( $settings['form_description'] )
		? $settings['form_description']
		: 'Fill out the form below and our team will get back to you.';


	/**
	 * Load frontend CSS.
	 */
	wp_enqueue_style(
		'fdp-public-style',
		FDP_URL . 'assets/css/style.css',
		array(),
		FDP_VERSION
	);


	/**
	 * Load frontend AJAX JavaScript.
	 */
	wp_enqueue_script(
		'fdp-form-script',
		FDP_URL . 'assets/js/form.js',
		array(),
		FDP_VERSION,
		true
	);


	wp_localize_script(
		'fdp-form-script',
		'fdpForm',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);


	ob_start();
	?>

	<div class="fdp-form-wrapper">

		<div class="fdp-form-header">

			<span class="fdp-form-eyebrow">
				GET IN TOUCH
			</span>

			<h2>
				<?php echo esc_html( $form_title ); ?>
			</h2>

			<p>
				<?php echo esc_html( $form_description ); ?>
			</p>

		</div>


		<form
			id="fdp-lead-form"
			class="fdp-lead-form"
			method="post"
		>

			<?php
			wp_nonce_field(
				'fdp_submit_lead',
				'fdp_form_nonce'
			);
			?>


			<div class="fdp-form-grid">

				<div class="fdp-form-field">

					<label for="fdp_name">
						Name *
					</label>

					<input
						type="text"
						id="fdp_name"
						name="fdp_name"
						placeholder="Your name"
						required
					>

				</div>


				<div class="fdp-form-field">

					<label for="fdp_email">
						Email *
					</label>

					<input
						type="email"
						id="fdp_email"
						name="fdp_email"
						placeholder="you@example.com"
						required
					>

				</div>


				<div class="fdp-form-field">

					<label for="fdp_phone">
						Phone
					</label>

					<input
						type="text"
						id="fdp_phone"
						name="fdp_phone"
						placeholder="+1 234 567 890"
					>

				</div>


				<div class="fdp-form-field">

					<label for="fdp_company">
						Company
					</label>

					<input
						type="text"
						id="fdp_company"
						name="fdp_company"
						placeholder="Company name"
					>

				</div>


				<div class="fdp-form-field fdp-form-full">

					<label for="fdp_budget">
						Project Budget
					</label>

					<input
						type="text"
						id="fdp_budget"
						name="fdp_budget"
						placeholder="Example: $2,000"
					>

				</div>

			</div>


			<div
				id="fdp-form-message"
				class="fdp-form-message"
			></div>


			<button
				type="submit"
				class="fdp-submit-button"
			>
				Submit Enquiry
			</button>

		</form>

	</div>
	
	<div class="fdp-hp-field" aria-hidden="true">
	<label for="fdp_website">
		Website
	</label>

	<input
		type="text"
		id="fdp_website"
		name="fdp_website"
		value=""
		tabindex="-1"
		autocomplete="off"
	>
</div>

	<?php

	return ob_get_clean();
}


add_shortcode(
	'flowdesk_form',
	'fdp_render_lead_form'
);

