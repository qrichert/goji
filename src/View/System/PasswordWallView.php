<main class="centered">
	<section class="centered">
		<p class="pre-heading"><?= $this->m_app->getSiteName(); ?></p>
		<h1><?= $tr->_('PASSWORD_WALL_MAIN_TITLE'); ?></h1>
	</section>

	<section class="centered no-padding h">
		<?php $form->render(); ?>
	<section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/Form-19.6.22.class.min.js'
	]);
?>
<script>
	(function() {

		// Log In

		let form = document.querySelector('#password-wall__form');
		let formError = document.querySelector('p.form__error');

		let success = response => {

			formError.textContent = '';
			location.reload();
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
