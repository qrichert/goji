<main>
	<section class="text">
		<h1><?= $tr->_('CONTACT_MAIN_TITLE'); ?></h1>

		<?php
			if ($formSentSuccess === true) {
				echo '<p id="form__status" class="form__success">' . $tr->_('CONTACT_SUCCESS') . '</p>';
			} else if ($formSentSuccess === false) {
				echo '<p id="form__status" class="form__error">' . $tr->_('CONTACT_ERROR') . '</p>';
			} else {
				echo '<p id="form__status"></p>';
			}
		?>

		<?php $form->render(); ?>

		<script src="js/lib/Goji/TextAreaAutoResize-19.6.6.class.min.js"></script>
		<script>
			(function () {
				new TextAreaAutoResize(document.querySelector('#contact__message'));
			})();
		</script>
	</section>

	<script src="js/lib/Goji/Form-19.6.22.class.min.js"></script>
	<script>
		(function() {

			let form = document.querySelector('form.form__contact');
			let formStatus = document.querySelector('p#form__status');

			let success = response => {

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
</main>
