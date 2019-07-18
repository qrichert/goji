<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_POST_MAIN_TITLE', $this->m_action); ?></h1>

		<?php
			if ($formSentSuccess === true) {

				$message = $this->m_action == \Goji\Blog\BlogPostManager::ACTION_UPDATE ?
					$tr->_('BLOG_POST_UPDATE_SUCCESS') :
					$tr->_('BLOG_POST_SUCCESS');

				echo '<p id="form__status" class="form__success">' . $message . '</p>';

			} else if ($formSentSuccess === false) {

				echo '<p id="form__status" class="form__error">' . $tr->_('BLOG_POST_ERROR') . '</p>';

			} else {

				echo '<p id="form__status"></p>';
			}
		?>

		<?php $blogPostManager->getForm()->render(); ?>

		<script src="../js/lib/Goji/TextAreaAutoResize-19.6.6.class.min.js"></script>
		<script>
			(function () {
				new TextAreaAutoResize(document.querySelector('#blog-post__post'));
			})();
		</script>
	</section>

	<script src="../js/lib/Goji/Form-19.6.22.class.min.js"></script>
	<script>
		(function() {

			let form = document.querySelector('form.form__blog-post');
			let formStatus = document.querySelector('p#form__status');
			let title = form.querySelector('#blog-post__title');
			let post = form.querySelector('#blog-post__post');

			let success = response => {

				// Clear message requested (create mode)
				if (typeof response.redirect !== 'undefined' && response.redirect !== false) {

					title.value = '';
					post.value = '';

					location.href = response.redirect;
				}

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
