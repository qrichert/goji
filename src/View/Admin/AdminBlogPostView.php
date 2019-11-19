<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_POST_MAIN_TITLE', $this->m_action); ?></h1>

		<?php
			if ($this->m_action == \Goji\Blog\BlogPostManager::ACTION_UPDATE) {

				$link = $this->m_app->getRouter()->getLinkForPage('blog') . '/' .
				        $blogPostManager->getForm()->getInputByName('blog-post[permalink]')->getValue();
			?>
				<div class="blog__toolbar toolbar toolbar__main">
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post') .
					             '?action=' . \Goji\Blog\BlogPostManager::ACTION_CREATE; ?>"
					   class="link-button highlight add" id="blog__toolbar--new-blog-post">
						<?= $tr->_('BLOG_POST_NEW_BLOG_POST'); ?>
					</a>
					<a href="<?= $link; ?>"
					   class="link-button" id="blog__toolbar--go-to-blog-post">
						<?= $tr->_('BLOG_POST_GO_TO_BLOG_POST'); ?>
					</a>
				</div>
			<?php
			}
		?>

		<?php $blogPostManager->getForm()->render(); ?>

	</section>
</main>

<?php
	$template->linkFiles([
		'js/lib/Goji/TextAreaAutoResize-19.6.6.class.min.js', // ../js/lib/.. if you use <script> tag in HTML
		'js/lib/Goji/Form-19.6.22.class.min.js'
	]);
?>
<script>
	(function () {

		<?php
			if ($this->m_action == \Goji\Blog\BlogPostManager::ACTION_CREATE) {
			?>
				let permalink = document.querySelector('#blog-post__permalink');
				let title = document.querySelector('#blog-post__title');
				let permalinkEdited = false;

				function formatPermalink(permalink) {

					permalink = permalink.toLowerCase();
					permalink = permalink.normalize("NFD").replace(/[\u0300-\u036f]/g, '');
					permalink = permalink.replace(/[^A-Z0-9]+/gi, '-');
					permalink = permalink.replace(/(^-+|-+$)/g, '');

					return permalink;
				}

				permalink.addEventListener('keyup', () => permalinkEdited = true, false);

				title.addEventListener('keyup', () => {

					if (permalinkEdited)
						return;

					permalink.value = formatPermalink(title.value);
				}, false);
			<?php
			}
		?>

		new TextAreaAutoResize(document.querySelector('#blog-post__post'));
	})();

	(function() {

		let form = document.querySelector('form.form__blog-post');
		let formSuccess = form.querySelector('p.form__success');
		let formError = form.querySelector('p.form__error');
		let permalink = document.querySelector('#blog-post__permalink');
		let linkBase = '<?= $this->m_app->getRouter()->getLinkForPage('blog') . '/'; ?>';
		let goToBlogPost = document.querySelector('#blog__toolbar--go-to-blog-post');
		let title = form.querySelector('#blog-post__title');
		let post = form.querySelector('#blog-post__post');

		let success = response => {

			// Clear message requested (create mode)
			if (typeof response.redirect !== 'undefined' && response.redirect !== false) {

				title.value = '';
				post.value = '';

				location.href = response.redirect;

				return;
			}

			formError.textContent = '';

			if (typeof response.message !== 'undefined') {
				formSuccess.innerHTML = response.message;
			}

			// Update permalink with sanitized one
			if (typeof response.permalink !== 'undefined') {
				goToBlogPost.href = linkBase + response.permalink;
				permalink.value = response.permalink;
			}
		};

		let error = response => {

			formSuccess.textContent = '';

			if (typeof response.message !== 'undefined') {
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
