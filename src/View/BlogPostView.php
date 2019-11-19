<main>
	<section class="text">
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
			?>
		</p>

		<?php
			if ($this->m_app->getUser()->isLoggedIn()
			    && $this->m_app->getMemberManager()->memberIs('editor')) {

				$editLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$editLink .= '?action=' . \Goji\Blog\BlogPostManager::ACTION_UPDATE;
					$editLink .= '&id=' . $blogPost['id'];

				$deleteLink = $this->m_app->getRouter()->getLinkForPage('admin-blog-post');
					$deleteLink .= '?action=' . \Goji\Blog\BlogPostManager::ACTION_DELETE;
					$deleteLink .= '&id=' . $blogPost['id'];
			?>
				<div class="blog__toolbar toolbar toolbar__main">
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

		<?php
			// Markdown or other formatting need to be rendered here
			//$md->text($blogPost['post']);
			echo '<p>', nl2br(htmlspecialchars($blogPost['post'])), '</p>';
		?>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('blog'); ?>"><?= $tr->_('BLOG_POST_BACK_TO_BLOG_POSTS'); ?></a>
		</p>
	</section>
</main>

<?php
	if ($this->m_app->getUser()->isLoggedIn()
	    && $this->m_app->getMemberManager()->memberIs('editor')) {
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
