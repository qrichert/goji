<main>
	<section class="text">
		<h1><?= $tr->_('BLOG_CATEGORIES_MAIN_TITLE'); ?></h1>

		<?php $blogCategoriesForm->render(); ?>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>"><?= $tr->_('GO_BACK_TO_ADMIN_AREA'); ?></a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/TextAreaAutoResize.class.min.js',
	'js/lib/Goji/Form.class.min.js'
]);
?>
<script>
	(function() {
		new TextAreaAutoResize(document.querySelector('#blog-categories__categories'));
	})();
</script>
