<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_MAIN_TITLE'); ?></h1>

		<?php
			if (empty($blogPosts))
				echo "<p>{$tr->_('BLOG_NO_BLOG_POSTS')}</p>";
		?>

		<?php
			$nbPosts = count($blogPosts);
			$i = 0;

			foreach ($blogPosts as $post) {
				$i++;
			?>
				<h2><a href="#"><?= $post['title']; ?></a></h2>
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
					<a href="#"><?= $tr->_('BLOG_READ_MORE'); ?></a>
				</p>

				<?= $i < $nbPosts ? '<hr>' : ''; ?>
			<?php
			}
		?>
	</section>
</main>
