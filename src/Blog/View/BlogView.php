<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_MAIN_TITLE'); ?></h1>

		<div class="blog__toolbar toolbar main-toolbar">
			<?php
			/*
				if (!empty($blogPosts))
					$blogSearchForm->render();
			*/
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


		<?php if (empty($blogPosts)): ?>

			<p><?= $tr->_('BLOG_NO_BLOG_POSTS'); ?></p>

		<?php else: ?>

			<div id="blog__blog-posts-list">

				<?php
				$nbPosts = count($blogPosts);
				$i = 0;
				$linkBase = $this->m_app->getRouter()->getLinkForPage('blog');

				// For SEO, display basic list of links
				foreach ($blogPosts as $post) {
					$i++;
					$link = $linkBase . '/' . $post['permalink'];

					echo '<a href="' . $link . '">' . $post['title'] . '</a>';
				}
				?>

			</div>
		<?php endif; ?>
	</section>
</main>

<?php if (!empty($blogPosts)): ?>

	<script>
		(function() {

			const LINK_BASE = '<?= addcslashes($this->m_app->getRouter()->getLinkForPage('blog'), "'"); ?>';
			const BLOG_POST_DATE_FORMAT = '<?= addcslashes($tr->_('BLOG_POST_DATE'), "'"); ?>';
			const READ_MORE = '<?= addcslashes($tr->_('BLOG_READ_MORE'), "'"); ?>';

			let blogPostsList = document.querySelector('#blog__blog-posts-list');

			/**
			 * @param {Object} blogPosts (JSON)
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

				// Can't be empty, otherwise this code wouldn't be executed and there would be no search bar
				// if (nbPosts === 0) {
				// 	return;
				// }

				let docFrag = document.createDocumentFragment();

				for (let i = 0; i < nbPosts; i++) {

					let post = blogPosts[i];
					let link = LINK_BASE + '/' + post.permalink;
					let date = BLOG_POST_DATE_FORMAT;
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
							blogPostPreviewLink.textContent = READ_MORE;
								blogPostPreview.appendChild(blogPostPreviewLink);

					if (i < nbPosts-1) {
						docFrag.appendChild(document.createElement('hr'));
					}
				}

				blogPostsList.appendChild(docFrag);
			};

			regenerateBlogPostList(JSON.parse('<?= addcslashes(json_encode($blogPosts), "'"); ?>'));

		})();
	</script>

<?php endif; ?>
