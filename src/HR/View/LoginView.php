<main class="centered">
	<h1 class="hidden" aria-hidden="true"><?= $tr->_('LOGIN_MAIN_TITLE'); ?></h1>

	<section class="centered no-padding h">

		<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="form__logo" aria-hidden="true">

		<?php $form->render(); ?>

		<div>
			<p>
				<?= $tr->_('LOGIN_NO_ACCOUNT'); ?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('sign-up'); ?>">
					<?= $tr->_('LOGIN_SIGN_UP'); ?>
				</a>
			</p>
		</div>

	</section>
</main>

<div class="dialog">
	<?php $resetPasswordRequestForm->render(); ?>
</div>

<?php
$template->linkFiles([
	'js/lib/Goji/Form.class.min.js',
	'js/lib/Goji/Dialog.class.min.js'
]);
?>
<script>
	(function() {

		// Log In

		let form = document.querySelector('#login__form');
		let formError = document.querySelector('p.form__error');

		let success = response => {

			formError.textContent = '';

			if (typeof response.redirect_to !== 'undefined'
			    && response.redirect_to !== null
			    && response.redirect_to !== '') {

				location.href = response.redirect_to;
			}
		};

		let error = response => {

			if (typeof response.message !== 'undefined'
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

	(function () {

		// Reset Password

		// Dialog
		let dialog = document.querySelector('.dialog');
		let triggerOpen = document.querySelector('#login__forgot-password');
		let inputEmail = dialog.querySelector('#reset-password-request__email');

		new Dialog(dialog, triggerOpen, null, () => { inputEmail.focus(); });

		// Form

		let form = document.querySelector('#reset-password-request__form');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');

		let success = response => {

			form.reset();
			formError.textContent = '';

			if (typeof response.message !== 'undefined'
			    && response.message !== null) {

				formSuccess.innerHTML = response.message;
			}
		};

		let error = response => {

			formSuccess.textContent = '';

			if (typeof response.message !== 'undefined'
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
