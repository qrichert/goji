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

		<?php
			if ($this->m_app->getUser()->isLoggedIn()) {

				$editLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$editLink .= '?action=' . \Goji\Blog\BlogPostManager::ACTION_UPDATE;
					$editLink .= '&id=' . $blogPost['id'];

				$deleteLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$deleteLink .= '?action=' . \Goji\Blog\BlogPostManager::ACTION_DELETE;
					$deleteLink .= '&id=' . $blogPost['id'];
			?>
				<div class="blog__toolbar">
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

		<!-- Remove <p> for Mardown -->
		<p>
			<?= nl2br(htmlspecialchars($blogPost['post'])); ?>
		</p>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('blog'); ?>"><?= $tr->_('BLOG_POST_BACK_TO_BLOG_POSTS'); ?></a>
		</p>
	</section>
</main>

<?php
	if ($this->m_app->getUser()->isLoggedIn()) {
	?>
		<script>
			(function () {

				let deleteButton = document.querySelector('#button__delete-blog-post');

				deleteButton.addEventListener('click', e => {

					<?php
						$confirmation = addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION'), '"');
						$input = addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION_INPUT'), '"');
						$string = addcslashes($tr->_('BLOG_POST_DELETE_CONFIRMATION_STRING'), "'");
					?>

					let response = prompt("<?= $confirmation; ?>", "<?= $input; ?>");

					if (response !== '<?= $string; ?>')
						e.preventDefault();

				}, false);

			})();
		</script>
	<?php
	}
?>
