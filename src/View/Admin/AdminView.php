<main>
	<section class="text">
		<h1><?= $tr->_('ADMIN_MAIN_TITLE'); ?></h1>

		<?php

// <EDITOR>
			if ($this->m_app->getMemberManager()->memberIs('editor')) {
			?>
				<h2><?= $tr->_('ADMIN_SECTION_EDITING'); ?></h2>
				<div class="action-item__wrapper">
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
				<div class="action-item__wrapper">
					<a class="action-item" id="admin-action__clear-cache">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/cache__clear.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_CLEAR_CACHE'); ?></span>
					</a>

					<a class="action-item" id="admin-action__update">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/git.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_UPDATE'); ?></span>
					</a>

					<a class="action-item" id="admin-action__back-up-database">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/database__back-up.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_BACK_UP_DATABASE'); ?></span>
					</a>
				</div>
			<?php
			}
			?>
	</section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/ActionItem.class.min.js'
	]);
?>
<?php
	// <ROOT>
	if ($this->m_app->getMemberManager()->memberIs('root')) {
	?>
		<script>
			// Clear cache
			(function() {

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

						// Command output
						let output = '';

						if (typeof r.nb_removed !== 'undefined' && r.nb_removed !== null)
							output += r.nb_removed + '\n';

						if (typeof r.space_saved !== 'undefined' && r.space_saved !== null)
							output += r.space_saved;

						clearCache.title = output;
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

			// Update
			(function () {

				let update = document.querySelector('#admin-action__update');
				let updateAction = new ActionItem(update);

				update.addEventListener('click', () => {

					updateAction.startAction();

					let error = () => {
						updateAction.endError();
					};

					let load = (r) => {

						if (r === null || r.status === 'ERROR') {
							error();
							return;
						}

						updateAction.endSuccess();

						// Command output
						if (typeof r.output !== 'undefined' && r.output !== null)
							update.title = r.output.trim();
					};

					let progress = (loaded, total) => {
						updateAction.setProgress(loaded/total);
					};

					SimpleRequest.get(
						'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-update') ?>',
						load,
						error,
						error,
						progress,
						{ get_json: true }
					);

				}, false);

			})();

			// Back-up DB
			(function () {

				let backUp = document.querySelector('#admin-action__back-up-database');
				let backUpAction = new ActionItem(backUp);

				backUp.addEventListener('click', () => {

					backUpAction.startAction();

					let error = () => {
						backUpAction.endError();
					};

					let load = (r) => {

						if (r === null || r.status === 'ERROR') {
							error();
							return;
						}

						backUpAction.endSuccess();
					};

					let progress = (loaded, total) => {
						backUpAction.setProgress(loaded/total);
					};

					SimpleRequest.get(
						'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-back-up-database') ?>',
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
