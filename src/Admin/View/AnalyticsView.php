<main>
	<section class="text">
		<h1><?= $tr->_('ANALYTICS_MAIN_TITLE'); ?></h1>

		<div class="toolbar main-toolbar">
			<?php $analyticsForm->render(); ?>
		</div>

		<div id="analytics__loading-in-progress" class="loading-dots"></div>

		<canvas id="analytics__chart"></canvas>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>"><?= $tr->_('ANALYTICS_BACK_TO_ADMIN_AREA'); ?></a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/vendor/Chart.bundle.min.js'
]);
?>
<script>
	(function() {

		let chartCtx = document.querySelector('#analytics__chart').getContext('2d');
		let chart = null;
		let chartColorHighlight = getComputedStyle(document.body).getPropertyValue('--color-highlight');
		let chartColorHighlightHalo = getComputedStyle(document.body).getPropertyValue('--color-highlight-halo');

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
				regenerateChart(null);
			};

			let load = (r, s) => {

				stopLoading();

				if (r === null || s !== 200) {
					error();
					return;
				}

				if (typeof r.data === 'undefined' || r.data === null)
					r.data = [];

				regenerateChart(r.data);
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

		let regenerateChart = (pageviewData) => {

			if (pageviewData === null) {
				alert('no data');
				return;
			}

			if (chart !== null)
				chart.destroy();

			chart = new Chart(chartCtx, {
				// The type of chart we want to create
				type: 'line',

				// The data for our dataset
				data: {
					labels: pageviewData.snapshot_date,
					datasets: [{
						label: '<?= addcslashes($tr->_('ANALYTICS_LABEL_PAGE_VIEWS'), "'"); ?>',
						backgroundColor: chartColorHighlightHalo,
						borderColor: chartColorHighlight,
						pointBorderColor: chartColorHighlight,
						pointBackgroundColor: chartColorHighlight,
						data: pageviewData.nb_views,
						// cubicInterpolationMode: 'monotone',
						// lineTension: 0.3
					}]
				},

				// Configuration options go here
				options: {}
			});
		};

		selectionChange();

	})();
</script>
