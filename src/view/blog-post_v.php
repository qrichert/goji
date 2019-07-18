<main>
	<section class="text">
		<h1><?= $blogPost['title']; ?></h1>
		<p class="sub-heading">
			<?php
				$date = $tr->_('BLOG_POST_DATE');
					$date = str_replace('%{YEAR}', str_pad($blogPost['date']['year'], 2, '0', STR_PAD_LEFT), $date);
					$date = str_replace('%{MONTH}', str_pad($blogPost['date']['month'], 2, '0', STR_PAD_LEFT), $date);
					$date = str_replace('%{DAY}', str_pad($blogPost['date']['day'], 2, '0', STR_PAD_LEFT), $date);

					$date = str_replace('%{HOUR}', str_pad($blogPost['date']['hour'], 2, '0', STR_PAD_LEFT), $date);
					$date = str_replace('%{MIN}', str_pad($blogPost['date']['min'], 2, '0', STR_PAD_LEFT), $date);
					$date = str_replace('%{SEC}', str_pad($blogPost['date']['sec'], 2, '0', STR_PAD_LEFT), $date);

				echo $date;
			?>
		</p>

		<p>
			<?= nl2br(htmlspecialchars($blogPost['post'])); ?>
		</p>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('blog'); ?>"><?= $tr->_('BLOG_POST_BACK_TO_BLOG_POSTS'); ?></a>
		</p>
	</section>
</main>
