<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_MAIN_TITLE'); ?></h1>

		<div class="blog__toolbar toolbar main-toolbar">
			<?php
				if (!empty($blogPosts))
					$blogSearchForm->render();
			?>

			<?php if ($this->m_app->getUser()->isLoggedIn()
			    && $this->m_app->getMemberManager()->memberIs('editor')): ?>

				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post') .
			             '?action=' . \Blog\Model\BlogPostManager::ACTION_CREATE; ?>"
					class="link-button highlight add">
					<?= $tr->_('BLOG_NEW_BLOG_POST'); ?>
				</a>

			<?php endif; ?>
		</div>

		<div id="blog__blog-posts-loading-in-progress" class="loading-dots"></div>

		<div id="blog__blog-posts-list">
			<?php if (empty($blogPosts)): ?>

				<p><?= $tr->_('BLOG_NO_BLOG_POSTS'); ?></p>

			<?php else: ?>

					<?php
					$nbPosts = count($blogPosts);
					$i = 0;
					$linkBase = $this->m_app->getRouter()->getLinkForPage('blog');

					echo '<ul>';
					// For SEO, display basic list of links
					foreach ($blogPosts as $post) {
						$i++;
						$link = $linkBase . '/' . $post['permalink'];

						echo '<li><a href="' . $link . '">' . $post['title'] . '</a></li>';
					}
					echo '</ul>';
					?>

			<?php endif; ?>
		</div>
	</section>
</main>

<?php if (!empty($blogPosts)): ?>

	<script>
		(function() {

			const LINK_BASE = '<?= addcslashes($this->m_app->getRouter()->getLinkForPage('blog'), "'"); ?>';
			const DATE_FORMAT = '<?= addcslashes($tr->_('BLOG_POST_DATE'), "'"); ?>';
			const READ_MORE = '<?= addcslashes($tr->_('BLOG_READ_MORE'), "'"); ?>';
			const NOTHING_FOUND = '<?= addcslashes($tr->_('BLOG_NOTHING_FOUND'), "'"); ?>';

			let blogPostsList = document.querySelector('#blog__blog-posts-list');

			// Articles with no specific search, loaded as default
			let defaultArticles = <?= json_encode($blogPosts); ?>;

			let form = document.querySelector('#form__blog-search');
			let formQuery = document.querySelector('#blog-search__query');

			let loadingInProgress = document.querySelector('#blog__blog-posts-loading-in-progress');
			let loadingQueueLength = 0;

			let startLoading = () => {
				loadingInProgress.classList.add('loading');
			};

			let stopLoading = () => {
				loadingInProgress.classList.remove('loading');
			};

			/**
			 * @param {Object} blogPosts (JSON)
			 * @param {Boolean} replaceOldContent
			 */
			let regenerateBlogPostList = (blogPosts, replaceOldContent = true) => {

				if (replaceOldContent) {
					while (blogPostsList.firstChild)
						blogPostsList.removeChild(blogPostsList.firstChild);
				} else {
					if (blogPostsList.firstChild)
						blogPostsList.appendChild(document.createElement('hr'));
				}

				let nbPosts = blogPosts.length;

				// Search: If no blog posts matching query have been found
				// Can't be empty on load, otherwise this code wouldn't be executed and there would be no search bar
				if (nbPosts === 0) {

					// Always replace content in this case
					while (blogPostsList.firstChild)
						blogPostsList.removeChild(blogPostsList.firstChild);

					let nothingFound = document.createElement('p');
						nothingFound.textContent = NOTHING_FOUND;
							blogPostsList.appendChild(nothingFound);

					return;
				}

				let docFrag = document.createDocumentFragment();

				for (let i = 0; i < nbPosts; i++) {

					let post = blogPosts[i];
					let link = LINK_BASE + '/' + post.permalink;
					let date = DATE_FORMAT;
						date = date.replace('%{YEAR}', post.creation_date.year);
						date = date.replace('%{MONTH}', post.creation_date.month);
						date = date.replace('%{DAY}', post.creation_date.day);

					let blogPostTitle = document.createElement('h2');
						docFrag.appendChild(blogPostTitle);

						let blogPostTitleLink = document.createElement('a');
							blogPostTitleLink.href = link;
							blogPostTitleLink.textContent = post.title;
								blogPostTitle.appendChild(blogPostTitleLink);

					let blogPostDate = document.createElement('p');
						blogPostDate.classList.add('sub-heading');
						blogPostDate.textContent = date;
							docFrag.appendChild(blogPostDate);

					let blogPostPreview = document.createElement('p');
						blogPostPreview.appendChild(document.createTextNode(post.post));
							docFrag.appendChild(blogPostPreview);

						let blogPostPreviewLink = document.createElement('a');
							blogPostPreviewLink.href = link;
							blogPostPreviewLink.textContent = ' ' + READ_MORE;
								blogPostPreview.appendChild(blogPostPreviewLink);

					if (i < nbPosts-1) {
						docFrag.appendChild(document.createElement('hr'));
					}
				}

				blogPostsList.appendChild(docFrag);
			};

			regenerateBlogPostList(defaultArticles);


			let fetchArticlesForQuery = (query) => {

				let load = (r, s) => {

					loadingQueueLength--;

					if (loadingQueueLength < 0)
						loadingQueueLength = 0;

					if (loadingQueueLength === 0)
						stopLoading();


					// Without that late no-results response would overwrite default articles list
					if (formQuery.value === '')
						return;

					if (r === null || s !== 200)
						return;

					if (typeof r.posts === 'undefined' || !Array.isArray(r.posts))
						r.posts = [];

					regenerateBlogPostList(r.posts);
				};

				startLoading();
				loadingQueueLength++;

				SimpleRequest.post(
					'<?= $this->m_app->getRouter()->getLinkForPage('xhr-blog') ?>',
					new FormData(form),
					load,
					null,
					null,
					null,
					{ get_json: true }
				);
			};

			formQuery.addEventListener('keyup', () => {
				if (formQuery.value === '')
					regenerateBlogPostList(defaultArticles);
				else
					fetchArticlesForQuery(formQuery.value);
			}, false);

		})();
	</script>

<?php endif; ?>
