<main>
	<section class="text">
		<h1><?= $tr->_('ADMIN_MAIN_TITLE'); ?></h1>

		<?php
// <ROOT>
			if ($this->m_app->getMemberManager()->memberIs('root')) {
			?>
				<h2>Root</h2>
				<div>
					<a class="action-item" id="admin__clear-cache">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/trashcan.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption">Clear application cache</span>
					</a>
				</div>
			<?php
			}
			?>
	</section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/ActionItem-19.11.20.class.min.js'
	]);
?>
<?php
	// <ROOT>
	if ($this->m_app->getMemberManager()->memberIs('root')) {
	?>
		<script>
			(function() {

				// Clear cache

				let clearCache = document.querySelector('#admin__clear-cache');

				let clearCacheAction = new ActionItem(clearCache);

				clearCache.addEventListener('click', () => {

					clearCacheAction.startAction();

					let error = () => {
						clearCacheAction.endError();
					};

					let load = (r) => {

						if (r === null || r.status === 'ERROR') {
							error();
							return;
						}

						clearCacheAction.endSuccess();
					};

					let progress = (loaded, total) => {
						clearCacheAction.setProgress(loaded/total);
					};

					SimpleRequest.get(
						'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-clear-cache') ?>',
						load,
						error,
						error,
						progress,
						{ get_json: true }
					);

				}, false);

			})();
		</script>
	<?php
	}
?>
