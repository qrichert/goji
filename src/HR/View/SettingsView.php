<main>
	<section class="text">

		<h1><?= $tr->_('SETTINGS_MAIN_TITLE'); ?></h1>

		<h2><?= $tr->_('SETTINGS_PROFILE'); ?></h2>

		<?php $settingsProfileForm->render(); ?>

		<h2><?= $tr->_('SETTINGS_PASSWORD'); ?></h2>

		<?php $settingsPasswordForm->render(); ?>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>">
				<?= $tr->_('GO_BACK_TO_ADMIN_AREA'); ?>
			</a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/Form.class.min.js',
	'js/lib/Goji/PasswordsMatch.class.min.js'
]);
?>

<script>
	// Profile
	(function () {

		let form = document.querySelector('#settings__form--profile');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');

		let success = response => {

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

	// Password
	(function() {

		let form = document.querySelector('#settings__form--password');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');

		new PasswordsMatch(
			form.querySelector('#settings__password'),
			form.querySelector('#settings__password-confirmation'),
			'<?= addcslashes($tr->_('SETTINGS_FORM_ERROR_PASSWORDS_MUST_MATCH'), "'"); ?>'
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
</script>
