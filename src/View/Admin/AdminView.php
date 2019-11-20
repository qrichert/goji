<main>
	<section class="text">
		<h1><?= $tr->_('ADMIN_MAIN_TITLE'); ?></h1>

		<?php

// <EDITOR>
			if ($this->m_app->getMemberManager()->memberIs('editor')) {
			?>
				<h2><?= $tr->_('ADMIN_SECTION_EDITING'); ?></h2>
				<div>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post') .
					             '?action=' . \Goji\Blog\BlogPostManager::ACTION_CREATE; ?>"
					   class="action-item" id="admin-action__new-blog-post">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/typewriter.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_NEW_BLOG_POST'); ?></span>
					</a>
				</div>
			<?php
			}

// <ROOT>
			if ($this->m_app->getMemberManager()->memberIs('root')) {
			?>
				<h2><?= $tr->_('ADMIN_SECTION_ROOT'); ?></h2>
				<div>
					<a class="action-item" id="admin-action__clear-cache">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/clear-cache.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_CLEAR_CACHE'); ?></span>
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

				let clearCache = document.querySelector('#admin-action__clear-cache');

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
