<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register FlowDesk settings.
 */
function fdp_register_settings() {

	register_setting(
		'fdp_settings_group',
		'fdp_settings',
		array(
			'sanitize_callback' => 'fdp_sanitize_settings',
			'default'           => array(),
		)
	);
}

add_action(
	'admin_init',
	'fdp_register_settings'
);


/**
 * Sanitize settings before saving.
 */
function fdp_sanitize_settings( $input ) {

	$output = array();

	$output['notification_email'] = isset( $input['notification_email'] )
		? sanitize_email( $input['notification_email'] )
		: '';


	$output['email_notifications'] = isset( $input['email_notifications'] )
		? 1
		: 0;


	$allowed_statuses = array(
		'new',
		'contacted',
		'qualified',
		'won',
		'lost',
	);

	$default_status = isset( $input['default_status'] )
		? sanitize_key( $input['default_status'] )
		: 'new';

	$output['default_status'] = in_array(
		$default_status,
		$allowed_statuses,
		true
	)
		? $default_status
		: 'new';


	$output['form_title'] = isset( $input['form_title'] )
		? sanitize_text_field( $input['form_title'] )
		: 'Tell Us About Your Project';


	$output['form_description'] = isset( $input['form_description'] )
		? sanitize_textarea_field( $input['form_description'] )
		: 'Fill out the form below and our team will get back to you.';


	return $output;
}


/**
 * Render FlowDesk Settings page.
 */
function fdp_render_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}


	$settings = get_option(
		'fdp_settings',
		array()
	);


	$notification_email = isset( $settings['notification_email'] )
		? $settings['notification_email']
		: get_option( 'admin_email' );


	$email_notifications = isset( $settings['email_notifications'] )
		? (int) $settings['email_notifications']
		: 1;


	$default_status = isset( $settings['default_status'] )
		? $settings['default_status']
		: 'new';


	$form_title = isset( $settings['form_title'] )
		? $settings['form_title']
		: 'Tell Us About Your Project';


	$form_description = isset( $settings['form_description'] )
		? $settings['form_description']
		: 'Fill out the form below and our team will get back to you.';

	?>

	<div class="fdp-admin">

		<div class="fdp-header">

			<div>

				<p class="fdp-eyebrow">
					PLUGIN SETTINGS
				</p>

				<h1>
					FlowDesk Settings
				</h1>

				<p class="fdp-subtitle">
					Configure lead notifications and frontend form preferences.
				</p>

			</div>

		</div>


		<div class="fdp-settings-panel">

			<form
				method="post"
				action="options.php"
			>

				<?php
				settings_fields(
					'fdp_settings_group'
				);
				?>


				<div class="fdp-setting-section">

					<h2>
						Lead Notifications
					</h2>

					<p>
						Choose how FlowDesk handles new lead notifications.
					</p>


					<div class="fdp-setting-field">

						<label for="fdp_notification_email">
							Notification Email
						</label>

						<input
							type="email"
							id="fdp_notification_email"
							name="fdp_settings[notification_email]"
							value="<?php echo esc_attr( $notification_email ); ?>"
						>

					</div>


					<div class="fdp-setting-field">

						<label class="fdp-checkbox-label">

							<input
								type="checkbox"
								name="fdp_settings[email_notifications]"
								value="1"
								<?php checked( $email_notifications, 1 ); ?>
							>

							<span>
								Enable email notifications for new leads
							</span>

						</label>

					</div>

				</div>


				<div class="fdp-setting-section">

					<h2>
						Lead Settings
					</h2>

					<p>
						Choose the default status for new website leads.
					</p>


					<div class="fdp-setting-field">

						<label for="fdp_default_status">
							Default Lead Status
						</label>

						<select
							id="fdp_default_status"
							name="fdp_settings[default_status]"
						>

							<option
								value="new"
								<?php selected( $default_status, 'new' ); ?>
							>
								New
							</option>

							<option
								value="contacted"
								<?php selected( $default_status, 'contacted' ); ?>
							>
								Contacted
							</option>

							<option
								value="qualified"
								<?php selected( $default_status, 'qualified' ); ?>
							>
								Qualified
							</option>

							<option
								value="won"
								<?php selected( $default_status, 'won' ); ?>
							>
								Won
							</option>

							<option
								value="lost"
								<?php selected( $default_status, 'lost' ); ?>
							>
								Lost
							</option>

						</select>

					</div>

				</div>


				<div class="fdp-setting-section">

					<h2>
						Frontend Form
					</h2>

					<p>
						Customize the heading shown above the lead form.
					</p>


					<div class="fdp-setting-field">

						<label for="fdp_form_title">
							Form Title
						</label>

						<input
							type="text"
							id="fdp_form_title"
							name="fdp_settings[form_title]"
							value="<?php echo esc_attr( $form_title ); ?>"
						>

					</div>


					<div class="fdp-setting-field">

						<label for="fdp_form_description">
							Form Description
						</label>

						<textarea
							id="fdp_form_description"
							name="fdp_settings[form_description]"
							rows="4"
						><?php echo esc_textarea( $form_description ); ?></textarea>

					</div>

				</div>


				<?php submit_button( 'Save Settings' ); ?>

			</form>

		</div>

	</div>

	<?php
}