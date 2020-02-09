<main>
	<section class="text">
		<h1><?= $tr->_('CONTACT_MAIN_TITLE'); ?></h1>
	</section>
	<section class="side-by-side right-bigger reverse-on-squeeze">
		<div class="image">
			<img src="<?= $template->rsc('img/map.svg'); ?>" alt="" class="scalable rounded">
		</div>
		<div>
			<?php $inPageContentEdit->renderContent('HELP_MESSAGE', 'p', ['contact__help-message']); ?>

			<?php $form->render(); ?>
		</div>
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/TextAreaAutoResize.class.min.js',
	'js/lib/Goji/Form.class.min.js'
]);
?>
<script>
	(function () {
		new TextAreaAutoResize(document.querySelector('#contact__message'));
	})();

	(function() {

		let form = document.querySelector('form.form__contact');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');
		let message = form.querySelector('#contact__message'); // textarea

		let success = response => {

			// Clear message (textarea)
			message.value = '';

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
