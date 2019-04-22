<!DOCTYPE html>
<html lang="<?= CURRENT_LANGUAGE; ?>">
	<head>
		<!-- TODO: Add base tag so we can use urls with slashes /. Use App->getRequestHandler()->getRootFolder();
			Does this solve CSS relative path problem w/ SimpleMinifierCSS() ??
			<base href="/goji/public/">
		-->
		<!-- Analytics -->
		<?php
			if (!LOCAL_TESTING)
				require_once '../template/page/include/tracking_v.inc.php';
		?>

		<?php require_once '../template/page/include/head_v.inc.php'; ?>

		<!-- SEO -->
		<title><?= $_TEMPLATE->getPageTitle(); ?></title>
		<meta name="description" content="<?= $_TEMPLATE->getPageDescription(); ?>">
		<?= $_TEMPLATE->getRobotsBehaviour(); ?>
		<link rel="canonical" href="<?= SITE_URL; ?>/<?= PAGES[CURRENT_LANGUAGE][CURRENT_PAGE]; ?>">
		<?php

			foreach (ACCEPTED_LANGUAGES as $lang) {
				echo '<link rel="alternate" hreflang="' . $lang . '" href="' . SITE_URL . '/' . PAGES[$lang][CURRENT_PAGE] . '">' . PHP_EOL;
			}

			echo '<link rel="alternate" hreflang="x-default" href="' . SITE_URL . '/' . PAGES[DEFAULT_LANGUAGE][CURRENT_PAGE] . '">';
		?>

		<!-- Style -->
		<?php

			\Goji\Toolkit\SwissKnife::linkFiles('css', array(
				'css/main.css',
				'css/responsive.css'
			));

		?>

		<!-- Social -->
		<meta property="og:title" content="<?= $_TEMPLATE->getPageTitle(); ?>">
		<meta property="og:description" content="<?= $_TEMPLATE->getPageDescription(); ?>">
		<?php require_once '../template/page/include/opengraph_v.inc.php'; ?>

		<!-- Scripts -->
		<?php require_once '../template/page/include/head-javascript_v.inc.php'; ?>
	</head>
	<body id="<?= CURRENT_PAGE; ?>">
		<?php require_once '../template/page/include/body_v.inc.php'; ?>
		<?php require_once '../template/page/include/header_v.inc.php'; ?>

		<?= $_TEMPLATE->getPageContent(); ?>

		<?php require_once '../template/page/include/footer_v.inc.php'; ?>

		<?php require_once '../template/page/include/bottom-javascript_v.inc.php'; ?>
	</body>
</html>
