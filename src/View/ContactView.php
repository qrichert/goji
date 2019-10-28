<main>
	<section class="text">
		<h1><?= $tr->_('CONTACT_MAIN_TITLE'); ?></h1>

		<p id="form__status"></p>

		<?php $form->render(); ?>
	</section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/TextAreaAutoResize-19.6.6.class.min.js',
		'js/lib/Goji/Form-19.6.22.class.min.js'
	]);
?>
<script>
	(function () {
		new TextAreaAutoResize(document.querySelector('#contact__message'));
	})();

	(function() {

		let form = document.querySelector('form.form__contact');
		let formStatus = document.querySelector('p#form__status');
		let message = form.querySelector('#contact__message');

		let success = response => {

			// Clear message
			message.value = '';

			formStatus.classList.remove('form__error');
			formStatus.classList.add('form__success');
			formStatus.innerHTML = response.message;
		};

		let error = response => {

			if (response !== null) {
				formStatus.classList.remove('form__success');
				formStatus.classList.add('form__error');
				formStatus.innerHTML = response.message;
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
