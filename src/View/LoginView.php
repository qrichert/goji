<main class="centered">
	<h1 class="hidden" aria-hidden="true">Login</h1>

	<section class="centered no-padding h">

		<img src="img/goji__berries.svg" alt="" class="form__logo" aria-hidden="true">

		<?php $form->render(); ?>

		<div>
			<p><?= $tr->_('LOGIN_NO_ACCOUNT'); ?> <a href="#"><?= $tr->_('LOGIN_SIGN_UP'); ?></a></p>
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

		let form = document.querySelector('form.form__login');

		let success = response => {
			location.href = response.redirect_to;
		};

		let error = response => {

			if (typeof response.message !== 'undefined') {
				// Do something
			}
		};

		new Form(document.querySelector('form.form__login'),
				 success,
				 error,
				 form.querySelector('button.loader'),
				 form.querySelector('.progress-bar')
		);

	})();
</script>
