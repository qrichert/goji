<main>
	<section class="text">
		<h1><?= $tr->_('ADMIN_MAIN_TITLE'); ?></h1>

		<!-- <MEMBER> -->

		<div class="action-item__wrapper">
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('settings'); ?>"
			   class="action-item" id="admin-action__settings">
				<div class="action-item__progress"></div>
				<img src="<?= $template->rsc('img/lib/Goji/settings.svg'); ?>" alt="" class="action-item__icon">
				<span class="action-item__caption"><?= $tr->_('FOOTER_SETTINGS'); ?></span>
			</a>

			<a href="<?= $this->m_app->getRouter()->getLinkForPage('logout'); ?>"
			   class="action-item" id="admin-action__logout">
				<div class="action-item__progress"></div>
				<img src="<?= $template->rsc('img/lib/Goji/log-out.svg'); ?>" alt="" class="action-item__icon">
				<span class="action-item__caption"><?= $tr->_('FOOTER_LOG_OUT'); ?></span>
			</a>
		</div>

		<!-- <EDITOR> -->

		<?php if ($this->m_app->getMemberManager()->memberIs('editor')): ?>

			<h2><?= $tr->_('ADMIN_SECTION_EDITING'); ?></h2>
			<div class="action-item__wrapper">
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-contact'); ?>"
				   class="action-item" id="admin-action__contact"
				   data-unopened-mail-count="<?= $this->m_contactUnopenedMailCount; ?>">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/mail.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_CONTACT'); ?></span>
				</a>

				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-upload'); ?>"
				   class="action-item" id="admin-action__upload">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/upload.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_UPLOAD'); ?></span>
				</a>

				<?php if ($this->m_useBlog): ?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post') .
					             '?action=' . \Blog\Model\BlogPostManager::ACTION_CREATE; ?>"
					   class="action-item" id="admin-action__new-blog-post">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/typewriter.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_NEW_BLOG_POST'); ?></span>
					</a>

					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-categories'); ?>"
					   class="action-item" id="admin-action__edit-blog-categories">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/categories.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_EDIT_BLOG_CATEGORIES'); ?></span>
					</a>
				<?php endif; ?>
			</div>
			<p id="admin__disk-usage">
				<?= $tr->_('ADMIN_DISK_SPACE_USED'); ?>
				<span class="admin__disk-usage--space-used"></span>
				<a></a>
			</p>

		<?php endif; ?>

		<!-- <ADMIN> -->

		<?php if ($this->m_app->getMemberManager()->memberIs('admin')): ?>

			<h2><?= $tr->_('ADMIN_SECTION_ADMIN'); ?></h2>
			<div class="action-item__wrapper">
				<a class="action-item" id="admin-action__add-member">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/member__add.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_ADD_MEMBER'); ?></span>
				</a>

				<a class="action-item" id="admin-action__back-up-database">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/database__back-up.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_BACK_UP_DATABASE'); ?></span>
				</a>

				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-analytics'); ?>"
				   class="action-item" id="admin-action__analytics">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/analytics.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_ANALYTICS'); ?></span>
				</a>
			</div>

		<?php endif; ?>

		<!-- <ROOT> -->

		<?php if ($this->m_app->getMemberManager()->memberIs('root')): ?>

			<h2><?= $tr->_('ADMIN_SECTION_ROOT'); ?></h2>
			<div class="action-item__wrapper">
				<a class="action-item" id="admin-action__clear-cache">
					<div class="action-item__progress"></div>
					<img src="<?= $template->rsc('img/lib/Goji/cache__clear.svg'); ?>" alt="" class="action-item__icon">
					<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_CLEAR_CACHE'); ?></span>
				</a>

				<?php if ($this->m_useGit): ?>
					<a class="action-item" id="admin-action__update">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/git.svg'); ?>" alt="" class="action-item__icon">
						<span class="action-item__caption"><?= $tr->_('ADMIN_ACTION_UPDATE'); ?></span>
					</a>
				<?php endif; ?>

				<?php if (!empty($this->m_terminalPath)): ?>
					<a class="action-item" id="admin-action__terminal" data-href="<?= $this->m_terminalPath; ?>">
						<div class="action-item__progress"></div>
						<img src="<?= $template->rsc('img/lib/Goji/terminal.svg'); ?>" alt="" class="action-item__icon">
						<div class="action-item__caption"><?= $tr->_('ADMIN_ACTION_TERMINAL'); ?></div>
					</a>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</section>
</main>

<!-- <ADMIN> -->

<?php if ($this->m_app->getMemberManager()->memberIs('admin')): ?>

	<div id="admin-action__add-member--dialog">
		<?php $addMemberForm->render(); ?>
	</div>

<?php endif; ?>

<?php
$template->linkFiles([
	'js/lib/Goji/ActionItem.class.min.js',
	'js/lib/Goji/Form.class.min.js',
	'js/lib/Goji/Dialog.class.min.js',
	'js/lib/Goji/PasswordsMatch.class.min.js'
]);
?>

<!-- <ADMIN> -->

<?php if ($this->m_app->getMemberManager()->memberIs('editor')): ?>

	<script>
		// Unopened Mail
		(function () {

			let actionContact = document.querySelector('#admin-action__contact');
			let actionContactCaption = document.querySelector('#admin-action__contact > .action-item__caption');
			let unopenedMailCount = parseInt(actionContact.dataset.unopenedMailCount, 10);

			if (unopenedMailCount > 0) {

				if (unopenedMailCount > 100)
					unopenedMailCount = '99+';

				actionContactCaption.appendChild(document.createTextNode(` (${unopenedMailCount})`));
				actionContact.classList.add('attention-required');
			}
		})();

		// Disk usage
		(function () {
			let diskUsageUsedSpace = document.querySelector('#admin__disk-usage > span.admin__disk-usage--space-used');
			let diskUsageRefresh = document.querySelector('#admin__disk-usage > a');

			let diskSpaceUsedBytes = null;
			let diskSpaceUsedFormatted = null;

			let refreshDiskUsageDisplay = () => {
				diskUsageUsedSpace.textContent = (diskSpaceUsedFormatted || '???');
			};

			let loadingInProgress = false;

			let startLoading = () => {
				loadingInProgress = true;
				diskUsageRefresh.textContent = '(<?= $tr->_('REFRESH'); ?>...)';
			};

			let endLoading = () => {
				loadingInProgress = false;
				diskUsageRefresh.textContent = `(<?= $tr->_('REFRESH'); ?>)`;
				refreshDiskUsageDisplay();
			};

			endLoading();

			diskUsageRefresh.addEventListener('click', e => {
				e.preventDefault();
				refreshDiskUsage();
			}, false);

			let refreshDiskUsage = (refresh = true) => {

				if (loadingInProgress)
					return;

				startLoading();

				let error = () => {
					endLoading();
					diskUsageUsedSpace.textContent = '<?= $tr->_('ERROR'); ?>';
				};

				let load = (r, s) => {
					if (r === null || s !== 200) {
						error();
						return;
					}

					diskSpaceUsedBytes = r.used_bytes;
					diskSpaceUsedFormatted = r.used_formatted;

					endLoading();
				};

				SimpleRequest.get(
					'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-disk-space-usage'); ?>'
						+ (refresh === true ? '?refresh' : ''),
					load,
					error,
					error,
					null,
					{ get_json: true }
				);
			};

			refreshDiskUsage(false); // false, refresh from cache
		})();
	</script>

<?php endif; ?>

<?php if ($this->m_app->getMemberManager()->memberIs('admin')): ?>

	<script>
		// Add member
		(function() {

			// Dialog
			let addMember = document.querySelector('#admin-action__add-member');
			let addMemberAction = new ActionItem(addMember);
			let dialog = document.querySelector('#admin-action__add-member--dialog');

			new Dialog(dialog, addMember);

			// Form
			let form = document.querySelector('#admin-action__add-member--form');
			let formSuccess = form.querySelector('p.form__success');
			let formError = form.querySelector('p.form__error');

			new PasswordsMatch(
				form.querySelector('#add-member__password'),
				form.querySelector('#add-member__password-confirmation'),
				'<?= addcslashes($tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR_PASSWORDS_MUST_MATCH'), "'"); ?>'
			);

			let success = response => {

				form.reset();
				formError.textContent = '';

				if (response !== null
				    && typeof response.message !== 'undefined'
				    && response.message !== null) {

					formSuccess.innerHTML = response.message;
				}
			};

			let error = response => {

				formSuccess.textContent = '';

				if (response !== null
				    && typeof response.message !== 'undefined'
				    && response.message !== null) {

					formError.innerHTML = response.message;
				}
			};

			new Form(form,
				success,
				error,
				form.querySelector('button.loader'),
				form.querySelector('.progress-bar')
			);

		})();

		// Back-up DB
		(function() {

			let backUp = document.querySelector('#admin-action__back-up-database');
			let backUpAction = new ActionItem(backUp);

			backUp.addEventListener('click', e => {

				e.preventDefault();

				backUpAction.startAction();

				let error = () => {
					backUpAction.endError();
				};

				let load = (r, s) => {

					if (r === null || s !== 200) {
						error();
						return;
					}

					backUpAction.endSuccess();
				};

				let progress = (loaded, total) => {
					backUpAction.setProgress(loaded/total);
				};

				SimpleRequest.get(
					'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-back-up-database'); ?>',
					load,
					error,
					error,
					progress,
					{ get_json: true }
				);

			}, false);

		})();
	</script>

<?php endif; ?>

<!-- <ROOT> -->

<?php if ($this->m_app->getMemberManager()->memberIs('root')): ?>

	<script>
		// Clear cache
		(function() {

			let clearCache = document.querySelector('#admin-action__clear-cache');
			let clearCacheAction = new ActionItem(clearCache);

			clearCache.addEventListener('click', e => {

				e.preventDefault();

				clearCacheAction.startAction();

				let error = () => {
					clearCacheAction.endError();
				};

				let load = (r, s) => {

					if (r === null || s !== 200) {
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
					'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-clear-cache'); ?>',
					load,
					error,
					error,
					progress,
					{ get_json: true }
				);

			}, false);

		})();

		<?php if ($this->m_useGit): ?>

			// Update
			(function() {

				let update = document.querySelector('#admin-action__update');
				let updateAction = new ActionItem(update);

				let clearCache = document.querySelector('#admin-action__clear-cache');

				update.addEventListener('click', e => {

					e.preventDefault();

					updateAction.startAction();

					let error = () => {
						updateAction.endError();
					};

					let load = (r, s) => {

						if (r === null || s !== 200) {
							error();
							return;
						}

						clearCache.click();

						updateAction.endSuccess();

						// Command output
						if (typeof r.output !== 'undefined' && r.output !== null)
							update.title = r.output.trim();
					};

					let progress = (loaded, total) => {
						updateAction.setProgress(loaded/total);
					};

					SimpleRequest.get(
						'<?= $this->m_app->getRouter()->getLinkForPage('xhr-admin-update'); ?>',
						load,
						error,
						error,
						progress,
						{ get_json: true }
					);

				}, false);

			})();

		<?php endif; ?>

		<?php if (!empty($this->m_terminalPath)): ?>

			// Terminal
			(function() {

				let terminal = document.querySelector('#admin-action__terminal');
				let terminalHref = terminal.dataset.href;

				terminal.addEventListener('click', e => {
					e.preventDefault();
					open(terminalHref, '_blank');
				}, false);

			})();

		<?php endif; ?>
	</script>

<?php endif; ?>
