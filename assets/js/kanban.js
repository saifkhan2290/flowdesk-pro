document.addEventListener('DOMContentLoaded', function () {

	const columns = document.querySelectorAll('.fdp-kanban-cards');

	let draggedCard = null;
	let originalColumn = null;


	/**
	 * Update numbers shown on each Kanban column.
	 */
	function updateColumnCounts() {

		document.querySelectorAll('.fdp-kanban-column').forEach(function (column) {

			const cardsContainer = column.querySelector('.fdp-kanban-cards');

			const cards = cardsContainer.querySelectorAll('.fdp-kanban-card');

			const countElement = column.querySelector('.fdp-kanban-count');


			if (countElement) {
				countElement.textContent = cards.length;
			}


			const emptyMessage = cardsContainer.querySelector('.fdp-kanban-empty');


			if (cards.length === 0) {

				if (!emptyMessage) {

					const empty = document.createElement('div');

					empty.className = 'fdp-kanban-empty';

					empty.textContent = 'No leads';

					cardsContainer.appendChild(empty);
				}

			} else if (emptyMessage) {

				emptyMessage.remove();
			}

		});
	}


	/**
	 * Make each Lead card draggable.
	 */
	document.querySelectorAll('.fdp-kanban-card').forEach(function (card) {

		card.addEventListener('dragstart', function () {

			draggedCard = card;

			originalColumn = card.closest('.fdp-kanban-cards');

			card.classList.add('fdp-is-dragging');


			setTimeout(function () {
				card.style.opacity = '0.5';
			}, 0);

		});


		card.addEventListener('dragend', function () {

			card.classList.remove('fdp-is-dragging');

			card.style.opacity = '';

			columns.forEach(function (column) {
				column.classList.remove('fdp-drag-over');
			});


			draggedCard = null;

			originalColumn = null;

		});

	});


	/**
	 * Handle dropping cards into columns.
	 */
	columns.forEach(function (column) {

		column.addEventListener('dragover', function (event) {

			event.preventDefault();

			column.classList.add('fdp-drag-over');
		});


		column.addEventListener('dragleave', function () {

			column.classList.remove('fdp-drag-over');
		});


		column.addEventListener('drop', function (event) {

			event.preventDefault();

			column.classList.remove('fdp-drag-over');


			if (!draggedCard) {
				return;
			}


			const newStatus = column.dataset.status;

			const leadId = draggedCard.dataset.leadId;


			if (!newStatus || !leadId) {
				return;
			}


			const previousColumn = originalColumn;


			/**
			 * Move card visually.
			 */
			column.appendChild(draggedCard);

			updateColumnCounts();


			/**
			 * Send new status to WordPress.
			 */
			const formData = new URLSearchParams();

			formData.append(
				'action',
				'fdp_update_lead_status'
			);

			formData.append(
				'lead_id',
				leadId
			);

			formData.append(
				'status',
				newStatus
			);

			formData.append(
				'nonce',
				fdpPipeline.nonce
			);


			fetch(
				fdpPipeline.ajaxUrl,
				{
					method: 'POST',

					headers: {
						'Content-Type':
							'application/x-www-form-urlencoded; charset=UTF-8',
					},

					body: formData.toString(),
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
								: 'Unable to update lead.'
						);
					}

				})

				.catch(function (error) {

					alert(
						'Could not update lead status: ' +
						error.message
					);


					/**
					 * Put card back if AJAX fails.
					 */
					if (previousColumn) {

						previousColumn.appendChild(
							draggedCard
						);

						updateColumnCounts();
					}

				});

		});

	});


	updateColumnCounts();

});