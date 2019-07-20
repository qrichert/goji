<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_MAIN_TITLE'); ?></h1>

		<?php
			if ($this->m_app->getUser()->isLoggedIn()) {
			?>
				<div class="blog__toolbar">
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin-blog-post'); ?>"
						class="link-button highlight add">
						<?= $tr->_('BLOG_NEW_BLOG_POST'); ?>
					</a>
				</div>
			<?php
			}
		?>

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
							$date = str_replace('%{YEAR}', str_pad($post['date']['year'], 2, '0', STR_PAD_LEFT), $date);
							$date = str_replace('%{MONTH}', str_pad($post['date']['month'], 2, '0', STR_PAD_LEFT), $date);
							$date = str_replace('%{DAY}', str_pad($post['date']['day'], 2, '0', STR_PAD_LEFT), $date);

						echo $date;
					?>
				</p>
				<p>
					<?= htmlspecialchars($post['post']); ?>
					<a href="<?= $link ?>"><?= $tr->_('BLOG_READ_MORE'); ?></a>
				</p>

				<?= $i < $nbPosts ? '<hr>' : ''; ?>
			<?php
			}
		?>
	</section>
</main>
