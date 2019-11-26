<main class="centered">
	<h1 class="hidden" aria-hidden="true"><?= $tr->_('RESET_PASSWORD_MAIN_TITLE'); ?></h1>

	<section class="centered no-padding h">

		<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="form__logo" aria-hidden="true">

		<?php $resetPasswordForm->render(); ?>

		<div>
			<p>
				<?= $tr->_('RESET_PASSWORD_DONE'); ?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>">
					<?= $tr->_('RESET_PASSWORD_LOG_IN'); ?>
				</a>
			</p>
		</div>

	</section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/Form-19.6.22.class.min.js'
	]);
?>
<script>
	(function() {

		// Log In

		let form = document.querySelector('#reset-password__form');
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
</script>
