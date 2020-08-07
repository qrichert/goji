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
				categories.value = JSON.stringify(response.categories);
				rebuildCategoryEditionInterface(categories.value);
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

		const FORM_CATEGORY_INPUT_PLACEHOLDER = '<?= addcslashes($tr->_('BLOG_CATEGORIES_FORM_CATEGORY_INPUT_PLACEHOLDER'), "'"); ?>';

		const DELETE = '<?= addcslashes($tr->_('DELETE'), "'"); ?>';
		const DELETE_CONFIRM = '<?= addcslashes($tr->_('DELETE_CONFIRM'), "'"); ?>';

		let categoryEditionInterface = form.querySelector('#blog-categories__interface');
		let addCategoryButton = form.querySelector('#blog-categories__add-category');

		let addCategory = (category = null) => {

			let docFrag = document.createDocumentFragment();

				let categoryWrapper = document.createElement('div');
					docFrag.appendChild(categoryWrapper);

					let categoryId = document.createElement('input');
						categoryId.type = 'text';
						// categoryId.type = 'hidden';
						categoryId.name = 'blog-categories__categories[id][]';
						categoryId.value = category !== null ? category.id : null;
							categoryWrapper.appendChild(categoryId);

					let categoryName = document.createElement('input');
						categoryName.type = 'text';
						categoryName.name = 'blog-categories__categories[name][]';
						categoryName.placeholder = FORM_CATEGORY_INPUT_PLACEHOLDER;
						categoryName.value = category !== null ? category.name : null;
							categoryWrapper.appendChild(categoryName);

					let deleteCategoryButtonWrapper = document.createElement('p');
						deleteCategoryButtonWrapper.classList.add('blog-categories__delete-category');
							categoryWrapper.appendChild(deleteCategoryButtonWrapper);

						let deleteCategoryButton = document.createElement('a');
							deleteCategoryButton.textContent = DELETE;
								deleteCategoryButtonWrapper.appendChild(deleteCategoryButton);

							deleteCategoryButton.addEventListener('click', e => {
								e.preventDefault();
								removeCategory(categoryWrapper);
							}, false);

			categoryEditionInterface.appendChild(docFrag);
		};

		let removeCategory = el => {
			if (confirm(DELETE_CONFIRM))
				categoryEditionInterface.removeChild(el);
		};

		addCategoryButton.addEventListener('click', e => {
			e.preventDefault();
			addCategory();
		}, false);

		// Hydrating interface

		/**
		 * @param {JSON String} data
		 */
		let rebuildCategoryEditionInterface = data => {

			// Clearing existing data
			while (categoryEditionInterface.firstChild)
				categoryEditionInterface.removeChild(categoryEditionInterface.firstChild);

			try {
				data = JSON.parse(data);
			} catch (e) {
				data = [];
			}

			for (let category of data) {
				addCategory(category);
			}
		};

		rebuildCategoryEditionInterface(categories.value);
	})();
</script>
