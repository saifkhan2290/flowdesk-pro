document.addEventListener('DOMContentLoaded', function () {

	const form = document.getElementById('fdp-lead-form');

	if (!form) {
		return;
	}

	const message = document.getElementById('fdp-form-message');
	const button = form.querySelector('.fdp-submit-button');

	form.addEventListener('submit', function (event) {

		event.preventDefault();

		const formData = new FormData(form);

		formData.append(
			'action',
			'fdp_submit_lead'
		);

		button.disabled = true;
		button.textContent = 'Submitting...';

		message.style.display = 'none';
		message.className = 'fdp-form-message';


		fetch(
			fdpForm.ajaxUrl,
			{
				method: 'POST',
				body: formData
			}
		)

			.then(function (response) {
				return response.json();
			})

			.then(function (response) {

				if (!response.success) {

					throw new Error(
						response.data &&
						response.data.message
							? response.data.message
							: 'Something went wrong.'
					);
				}


				message.textContent =
					response.data.message;

				message.className =
					'fdp-form-message fdp-form-success';

				message.style.display = 'block';


				form.reset();

			})

			.catch(function (error) {

				message.textContent =
					error.message;

				message.className =
					'fdp-form-message fdp-form-error';

				message.style.display = 'block';

			})

			.finally(function () {

				button.disabled = false;

				button.textContent =
					'Submit Enquiry';

			});

	});

});