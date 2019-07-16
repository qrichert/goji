<!DOCTYPE html>
<html lang="<?= $this->m_app->getLanguages()->getCurrentHyphenLocale(); ?>">
	<head>
		<!-- Document -->
		<meta charset="utf-8">
		<!--<base href="<?= $this->m_app->getRequestHandler()->getRootFolder(); ?>">-->

		<!-- Analytics -->
		<?php
			if ($this->m_app->getAppMode() !== \Goji\Core\App::DEBUG)
				require_once '../template/page/include/analytics.template.inc.php';
		?>

		<?php require_once '../template/page/include/head.template.inc.php'; ?>

		<!-- SEO -->
		<title><?= $template->getPageTitle(); ?></title>
		<meta name="description" content="<?= $template->getPageDescription(); ?>">
		<?= $template->getRobotsBehaviour(); ?>
		<?php

			if ($template->getShowCanonicalPageAndAlternates()) {

				echo '<link rel="canonical" href="' . $this->m_app->getRouter()->getLinkForPage(null, null, true) . '">';

				foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

					echo '<link rel="alternate" hreflang="'
					     . $this->m_app->getLanguages()->hyphenateLocale($locale)
					     . '" href="' . $this->m_app->getRouter()->getLinkForPage(null, $locale, true)
					     . '">' . PHP_EOL;
				}

				echo '<link rel="alternate" hreflang="x-default" href="'
				     . $this->m_app->getRouter()->getLinkForPage(null, $this->m_app->getLanguages()->getFallbackLocale(), true)
				     . '">';
			}
		?>

		<!-- Style -->
		<?php

			// Put library files first, so you can overwrite them.
			$template->linkFiles([
				'css/root.css',
				'css/goji.css',
				'css/lib/Goji/inputs.css',
				'css/lib/Goji/flags.css',
				'css/main.css',
				'css/responsive.css'
			]);

		?>

		<!-- Social -->
		<?php require_once '../template/page/include/opengraph.template.inc.php'; ?>

		<!-- Scripts -->
		<?php require_once '../template/page/include/head-javascript.template.inc.php'; ?>
	</head>
	<body id="<?= $this->m_app->getRouter()->getCurrentPage(); ?>">
		<?php require_once '../template/page/include/header.template.inc.php'; ?>

		<?= $template->getPageContent(); ?>

		<?php require_once '../template/page/include/footer.template.inc.php'; ?>

		<?php require_once '../template/page/include/bottom-javascript.template.inc.php'; ?>
	</body>
</html>
