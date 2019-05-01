<!DOCTYPE html>
<html lang="<?= $this->m_app->getLanguages()->getCurrentHyphenLocale(); ?>">
	<head>
		<!-- Document -->
		<meta charset="utf-8">
		<base href="<?= $this->m_app->getRequestHandler()->getRootFolder(); ?>">

		<!-- Analytics -->
		<?php
			if ($this->m_app->getAppMode() !== \Goji\Core\App::DEBUG)
				require_once '../template/page/include/tracking_v.inc.php';
		?>

		<?php require_once '../template/page/include/head_v.inc.php'; ?>

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

			$template->linkFiles(array(
				'css/main.css',
				'css/responsive.css'
			));

		?>

		<!-- Social -->
		<meta property="og:title" content="<?= $template->getPageTitle(); ?>">
		<meta property="og:description" content="<?= $template->getPageDescription(); ?>">
		<?php require_once '../template/page/include/opengraph_v.inc.php'; ?>

		<!-- Scripts -->
		<?php require_once '../template/page/include/head-javascript_v.inc.php'; ?>
	</head>
	<body id="<?= $this->m_app->getRouter()->getCurrentPage(); ?>">
		<?php require_once '../template/page/include/body_v.inc.php'; ?>
		<?php require_once '../template/page/include/header_v.inc.php'; ?>

		<?= $template->getPageContent(); ?>

		<?php require_once '../template/page/include/footer_v.inc.php'; ?>

		<?php require_once '../template/page/include/bottom-javascript_v.inc.php'; ?>
	</body>
</html>
