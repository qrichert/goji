<main>
	<h1><?= HELLO_WORLD; ?></h1>

	<!-- URLs translated -->
	<p id="language-selector">
		<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'fr'); ?>">
			<?= $this->m_app->getLanguages()->getConfigurationLocales()['fr']; ?>
		</a> -
		<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'en_US'); ?>">
			<?= $this->m_app->getLanguages()->getConfigurationLocales()['en_US']; ?>
		</a> -
		<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'en_GB'); ?>">
			<?= $this->m_app->getLanguages()->getConfigurationLocales()['en_GB']; ?>
		</a>
	</p>
</main>
