<main>
	<section class="text">
		<h1><?= $tr->_('ANALYTICS_MAIN_TITLE'); ?></h1>

		<div class="toolbar main-toolbar">
			<?php $analyticsForm->render(); ?>
		</div>

		<div id="analytics__loading-in-progress" class="loading-dots"></div>

		<canvas id="analytics__chart" width="2" height="1"></canvas>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>"><?= $tr->_('ANALYTICS_BACK_TO_ADMIN_AREA'); ?></a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/vendor/Chart.bundle.min.js',
	'js/vendor/regression.min.js'
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

		let pageViewData = null;

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

				pageViewData = r.data;
				regenerateChart();
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

		let regenerateChart = () => {

			if (chart !== null)
				chart.destroy();

			if (pageViewData === null) {
				return;
			}

			// Convert dates to indexes, so we have numbers to work with mathematically
			let regressionValues = zip([...pageViewData.snapshot_date.keys()], pageViewData.nb_views);
				regressionValues = regression.linear(regressionValues).points; // TODO: regression.exponential() seems good too, look what works best with real world data

			// Keep only Y values
			for (let i = 0; i < regressionValues.length; i++) {
				regressionValues[i] = regressionValues[i][1];

				// Cap at 0, can't be negative
				if (regressionValues[i] < 0)
					regressionValues[i] = 0
			}

			chart = new Chart(chartCtx, {
				// The type of chart we want to create
				type: 'line',

				// The data for our dataset
				data: {
					labels: pageViewData.snapshot_date,
					datasets: [{
						data: pageViewData.nb_views,
						label: '<?= addcslashes($tr->_('ANALYTICS_LABEL_PAGE_VIEWS'), "'"); ?>',
						borderColor: chartColorHighlight,
						backgroundColor: chartColorHighlightHalo,
						pointBorderColor: chartColorHighlight,
						pointBackgroundColor: chartColorHighlight,
						// cubicInterpolationMode: 'monotone',
						// lineTension: 0.3
					}, {
						data: regressionValues,
						label: '<?= addcslashes($tr->_('ANALYTICS_LABEL_TRENDLINE'), "'"); ?>',
						borderWidth: 2,
						borderColor: '#ffdfe1',
						fill: false,
						pointRadius: 0,
						pointHoverRadius: 0,
					}]
				},

				// Configuration options go here
				options: {}
			});
		};

		selectionChange();

	})();
</script>
