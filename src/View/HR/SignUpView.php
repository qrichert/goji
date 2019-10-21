<main class="centered">
	<h1 class="hidden" aria-hidden="true">Login</h1>

	<section class="centered no-padding h">

		<img src="img/goji__berries.svg" alt="" class="form__logo" aria-hidden="true">

		<?php $form->render(); ?>

		<div>
			<p>
				<?= $tr->_('SIGN_UP_ALREADY_ACCOUNT'); ?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>">
					<?= $tr->_('SIGN_UP_LOG_IN'); ?>
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

		let form = document.querySelector('#sign-up__form');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');

		let success = response => {

			form.reset();
			formError.textContent = '';

			if (typeof response.message !== 'undefined') {
				formSuccess.innerHTML = response.message;
			}
		};

		let error = response => {

			formSuccess.textContent = '';

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
