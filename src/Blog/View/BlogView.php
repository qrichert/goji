<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_MAIN_TITLE'); ?></h1>

		<?php if ($this->m_app->getUser()->isLoggedIn()
		    && $this->m_app->getMemberManager()->memberIs('editor')): ?>

			<div class="blog__toolbar toolbar main-toolbar">
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post') .
			             '?action=' . \Blog\Model\BlogPostManager::ACTION_CREATE; ?>"
					class="link-button highlight add">
					<?= $tr->_('BLOG_NEW_BLOG_POST'); ?>
				</a>
			</div>

		<?php endif; ?>

		<?php
		if (empty($blogPosts))
			echo "<p>{$tr->_('BLOG_NO_BLOG_POSTS')}</p>";
		?>

		<?php
		$nbPosts = count($blogPosts);
		$i = 0;
		$linkBase = $this->m_app->getRouter()->getLinkForPage('blog');

		foreach ($blogPosts as $post) {
			$i++;
			$link = $linkBase . '/' . $post['permalink'];
		?>
			<h2><a href="<?= $link ?>"><?= $post['title']; ?></a></h2>
			<p class="sub-heading">
				<?php
				$date = $tr->_('BLOG_POST_DATE');
					$date = str_replace('%{YEAR}', $post['creation_date']['year'], $date);
					$date = str_replace('%{MONTH}', $post['creation_date']['month'], $date);
					$date = str_replace('%{DAY}', $post['creation_date']['day'], $date);

				echo $date;
				?>
			</p>
			<p>
				<?= $post['post']; ?>
				<a href="<?= $link ?>"><?= $tr->_('BLOG_READ_MORE'); ?></a>
			</p>

			<?= $i < $nbPosts ? '<hr>' : ''; ?>
		<?php
		}
		?>
	</section>
</main>
