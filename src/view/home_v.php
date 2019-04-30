<main>
	<h1><?= HELLO_WORLD; ?></h1>

	<!-- URLs translated -->
	<p id="language-selector">
		<?php

			$count = count($this->m_app->getLanguages()->getSupportedLocales());
			$i = 0;
			foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

				if ($locale == $this->m_app->getLanguages()->getCurrentLocale())
					continue;

				$i++;

				echo '<a href="' . $this->m_app->getRouter()->getLinkForPage(null, $locale) . '">'
				     . $this->m_app->getLanguages()->getConfigurationLocales()[$locale]
				     . '</a>',
					$i < $count - 1 ? ' - ' : '',
					PHP_EOL;
			}

			/* Or if you prefer to do it manually

			<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'fr'); ?>">
				<?= $this->m_app->getLanguages()->getConfigurationLocales()['fr']; ?>
			</a> -
			<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'en_US'); ?>">
				<?= $this->m_app->getLanguages()->getConfigurationLocales()['en_US']; ?>
			</a> -
			<a href="<?= $this->m_app->getRouter()->getLinkForPage(null, 'en_GB'); ?>">
				<?= $this->m_app->getLanguages()->getConfigurationLocales()['en_GB']; ?>
			</a>

			*/
		?>
	</p>
</main>
