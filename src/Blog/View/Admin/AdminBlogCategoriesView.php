<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_CATEGORIES_MAIN_TITLE'); ?></h1>

		<?php $blogCategoriesForm->render(); ?>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>"><?= $tr->_('GO_BACK_TO_ADMIN_AREA'); ?></a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/TextAreaAutoResize.class.min.js',
	'js/lib/Goji/Form.class.min.js'
]);
?>
<script>
	(function() {
		new TextAreaAutoResize(document.querySelector('#blog-categories__categories'));
	})();

	(function() {

		let form = document.querySelector('#form__blog-categories');
		let formError = form.querySelector('p.form__error');
		let categories = form.querySelector('#blog-categories__categories'); // textarea

		let success = response => {

			formError.textContent = '';

			if (response !== null
			    && typeof response.categories !== 'undefined'
			    && response.categories !== null) {

				// Use formatted & ordered categories
				categories.value = response.categories.join('\n');
			}
		};

		let error = response => {

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
