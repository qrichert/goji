<main>
	<section class="text">
		<h1><?= $tr->_('ANALYTICS_MAIN_TITLE'); ?></h1>

		<div class="toolbar main-toolbar">
			<?php $analyticsForm->render(); ?>
		</div>

		<div id="analytics__loading-in-progress" class="loading-dots"></div>
	</section>
</main>

<script>
	(function() {

		let form = document.querySelector('#analytics__form');
		let formSelectPage = form.querySelector('#analytics__page');
		let formSelectTimeFrame = form.querySelector('#analytics__time-frame');

		let loadingInProgress = document.querySelector('#analytics__loading-in-progress');

		let startLoading = () => {
			loadingInProgress.classList.add('loading');
		};

		let stopLoading = () => {
			loadingInProgress.classList.remove('loading');
		};

		formSelectPage.addEventListener('change', () => { selectionChange(); }, false);
		formSelectTimeFrame.addEventListener('change', () => { selectionChange(); }, false);

		let selectionChange = () => {

			let error = () => {
				stopLoading();
				regenerateGraph(null);
			};

			let load = (r, s) => {

				stopLoading();

				if (r === null || s !== 200) {
					error();
					return;
				}

				if (typeof r.data === 'undefined' || !Array.isArray(r.data))
					r.data = [];

				regenerateGraph(r.data);
			};

			startLoading();

			SimpleRequest.post(
				'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-analytics') ?>',
				new FormData(form),
				load,
				error,
				error,
				null,
				{ get_json: true }
			);
		};

		let regenerateGraph = (data) => {
			if (data === null) {
				alert('no data');
				return;
			}
			alert('gen graph');
		};

		selectionChange();

	})();
</script>
