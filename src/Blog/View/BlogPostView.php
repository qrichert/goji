<main>
	<article>
		<section class="text">
			<?php if (!empty($blogPost['illustration'])): ?>
				<img src="<?= $blogPost['illustration']; ?>" alt="<?= $blogPost['title']; ?>" class="blog__illustration">
			<?php endif; ?>

			<h1><?= $blogPost['title']; ?></h1>
			<p class="sub-heading">
				<?php
				$date = $tr->_('BLOG_POST_DATE');
					$date = str_replace('%{YEAR}', $blogPost['creation_date']['year'], $date);
					$date = str_replace('%{MONTH}', $blogPost['creation_date']['month'], $date);
					$date = str_replace('%{DAY}', $blogPost['creation_date']['day'], $date);

					$date = str_replace('%{HOUR}', $blogPost['creation_date']['hour'], $date);
					$date = str_replace('%{MIN}', $blogPost['creation_date']['min'], $date);
					$date = str_replace('%{SEC}', $blogPost['creation_date']['sec'], $date);

				echo $date;

				if (!empty($blogPost['created_by_display_name']))
					echo " - {$tr->_('BY')} {$blogPost['created_by_display_name']}";
				?>
			</p>

			<?php
			if ($this->m_app->getUser()->isLoggedIn()
			    && $this->m_app->getMemberManager()->memberIs('editor')) {

				$editLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$editLink .= '?action=' . \Blog\Model\BlogPostManager::ACTION_UPDATE;
					$editLink .= '&id=' . $blogPost['id'];

				$deleteLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$deleteLink .= '?action=' . \Blog\Model\BlogPostManager::ACTION_DELETE;
					$deleteLink .= '&id=' . $blogPost['id'];
			?>
				<div class="blog__toolbar toolbar main-toolbar">
					<a href="<?= $editLink; ?>"
						class="link-button highlight">
						<?= $tr->_('EDIT'); ?>
					</a>
					<a href="<?= $deleteLink; ?>"
					   class="link-button delete"
					   id="button__delete-blog-post">
						<?= $tr->_('DELETE'); ?>
					</a>
				</div>
			<?php
			}
			?>

			<?php if ($blogPost['hidden']): ?>
				<p class="visible-for-editors-only">
					<?= str_replace('%{DATE}', $date, $tr->_('BLOG_POST_VISIBLE_FOR_EDITORS_ONLY')); ?>
				</p>
			<?php endif; ?>

			<p><?= $blogPost['post']; ?></p>

			<?php if (!empty($blogPost['previous']) || !empty($blogPost['next'])): ?>
				<div class="blog__previous-and-next">

					<?php if (!empty($blogPost['previous'])): ?>
						<p class="call-to-action__wrapper previous">
							<a href="<?= $blogPost['previous']['permalink']; ?>" class="call-to-action smaller transparent left">
								<?= $blogPost['previous']['title']; ?>
							</a>
						</p>
					<?php endif; ?>

					<?php if (!empty($blogPost['next'])): ?>
						<p class="call-to-action__wrapper next">
							<a href="<?= $blogPost['next']['permalink']; ?>" class="call-to-action smaller transparent">
								<?= $blogPost['next']['title']; ?>
							</a>
						</p>
					<?php endif; ?>

				</div>
			<?php endif; ?>

			<p>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('blog'); ?>"><?= $tr->_('BLOG_POST_BACK_TO_BLOG_POSTS'); ?></a>
			</p>
		</section>
	</article>
</main>

<?php if ($this->m_app->getUser()->isLoggedIn()
          && $this->m_app->getMemberManager()->memberIs('editor')): ?>

	<script>
		(function() {
			const CONFIRMATION = '<?= addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION'), "'"); ?>';
			const CONFIRMATION_INPUT = '<?= addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION_INPUT'), "'"); ?>';
			const CONFIRMATION_STRING = '<?= addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION_STRING'), "'"); ?>';

			let deleteButton = document.querySelector('#button__delete-blog-post');

			deleteButton.addEventListener('click', e => {

				let response = prompt(CONFIRMATION, CONFIRMATION_INPUT);

				if (response !== CONFIRMATION_STRING)
					e.preventDefault();

			}, false);
		})();
	</script>

<?php endif; ?>
