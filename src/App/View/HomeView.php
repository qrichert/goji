<main>
	<section class="text">
		<p class="pre-heading"><?= $tr->_('HOME_PRE_HEADING'); ?></p>
		<h1><?= $tr->_('HELLO_WORLD'); ?></h1>

		<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="Goji" width="150px">

		<h2><?= $tr->_('HOME_BERRIES'); ?></h2>

		<p class="pluralization-example"><?=
			$tr->_('HOME_PLURALIZATION', 0) . '<br>',
			$tr->_('HOME_PLURALIZATION', 1) . '<br>',
			$tr->_('HOME_PLURALIZATION', 2) . '<br>',
			$tr->_('HOME_PLURALIZATION', 42) . '<br>';
		?></p>

		<?php
		$homeIntro = $tr->_('HOME_TIRED_OF_NOT_HAVING_BERRIES');
		$homeIntro = \Goji\Rendering\TemplateExtensions::ctaToHTML(
			$homeIntro,
			$this->m_app->getRouter()->getLinkForPage('offer-1-landing-page')
		);

		echo $homeIntro;
		?>

		<?php $inPageContentEdit->renderContent('IN_PAGE_CONTENT_EDIT_DEMO_TITLE', 'h2'); ?>

		<?php $inPageContentEdit->renderContent('IN_PAGE_CONTENT_EDIT_DEMO_TEXT'); ?>

		<h2><?= $tr->_('HOME_TRY_A_DIFFERENT_LANGUAGE'); ?></h2>

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
