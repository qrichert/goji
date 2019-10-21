<main class="centered">
	<h1 class="hidden" aria-hidden="true">Login</h1>

	<section class="centered no-padding h">

		<img src="img/goji__berries.svg" alt="" class="form__logo" aria-hidden="true">

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
	<?php $resetPasswordForm->render(); ?>
</div>

<?php
	$template->linkFiles([
		'js/lib/Goji/Form-19.6.22.class.min.js',
		'js/lib/Goji/Dialog-19.10.21.class.js'
	]);
?>
<script>
	(function () {

		let dialog = document.querySelector('.dialog');
		let triggerOpen = document.querySelector('#login__forgot-password');

		new Dialog(dialog, triggerOpen);
	})();

	(function() {

		let form = document.querySelector('form.form__centered');
		let formError = document.querySelector('p.form__error');

		let success = response => {
			formError.textContent = '';
			location.href = response.redirect_to;
		};

		let error = response => {

			if (typeof response.message !== 'undefined') {
				formError.innerHTML = response.message;
			}
		};

		new Form(document.querySelector('form.form__centered'),
				 success,
				 error,
				 form.querySelector('button.loader'),
				 form.querySelector('.progress-bar')
		);

	})();
</script>
