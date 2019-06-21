<main>
	<section class="text">
		<p class="pre-heading">Welcome, and</p>
		<h1><?= $tr->_('HELLO_WORLD'); ?></h1>

		<img src="img/goji__berries.svg" alt="Goji" width="150px">

		<h2><?= $tr->_('HOME_FOOBAR') ?></h2>

		<p class="pluralization-example"><?=
			$tr->_('HOME_PLURALIZATION', 0) . '<br>' .
			$tr->_('HOME_PLURALIZATION', 1) . '<br>' .
			$tr->_('HOME_PLURALIZATION', 2) . '<br>' .
			$tr->_('HOME_PLURALIZATION', 42) . '<br>';
		?></p>

		<h2><?= $tr->_('HOME_TRY_A_DIFFERENT_LANGUAGE') ?></h2>

		<!-- URLs translated -->
		<p>
			👉&nbsp&nbsp
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
	</section>
</main>
