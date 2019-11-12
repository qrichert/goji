<!DOCTYPE html>
<html lang="<?= $this->m_app->getLanguages()->getCurrentHyphenLocale(); ?>">
	<head>
		<!-- Document -->
		<meta charset="utf-8">
		<!--<base href="<?= $this->m_app->getRequestHandler()->getRootFolder(); ?>">-->

		<!-- SEO -->
		<title><?= $template->getPageTitle(); ?></title>
		<meta name="description" content="<?= $template->getPageDescription(); ?>">
		<!--<link type="text/plain" rel="author" href="<?= $template->rsc('humans.txt'); ?>">-->
		<?= $template->getRobotsBehaviour(); ?>
		<?php
			// Remove the 'if {}' block if you don't use the blog
			if ($this->m_app->getRouter()->getCurrentPage() == 'blog-post') {

				if ($template->getShowCanonicalPageAndAlternates()) {

					// Add blog permalink ($this->m_permalink)
					echo '<link rel="canonical" href="' . $this->m_app->getRouter()->getLinkForPage(null, null, true, 0, [$this->m_permalink]) . '">';
				}

			} else {

				// Keep from here...

				if ($template->getShowCanonicalPageAndAlternates()) {

					echo '<link rel="canonical" href="' . $this->m_app->getRouter()->getLinkForPage(null, null, true) . '">', PHP_EOL;

					foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

						echo '<link rel="alternate" hreflang="'
						     . $this->m_app->getLanguages()->hyphenateLocale($locale)
						     . '" href="' . $this->m_app->getRouter()->getLinkForPage(null, $locale, true)
						     . '">', PHP_EOL;
					}

					echo '<link rel="alternate" hreflang="x-default" href="'
					     . $this->m_app->getRouter()->getLinkForPage(null, $this->m_app->getLanguages()->getFallbackLocale(), true)
					     . '">';
				}

				// To here
			}
		?>

		<?php require_once $template->getTemplate('page/include/head'); ?>

		<!-- Style -->
		<?php
			// Put library files first, so you can overwrite them.
			$cssFiles = [
				'css/reset.css',
				'css/root.css',
				'css/goji.css',
				//'css/lib/Goji/books.css',
				'css/lib/Goji/flags.css',
				'css/lib/Goji/inputs.css',
				'css/main.css',
				'css/responsive.css'
			];

			// Add alternate 'root.css'
			if ($template->getSpecial('is-funnel-page')) {

				if ($template->getLinkedFilesMode() == \Goji\Rendering\SimpleTemplate::MERGED)
					// In merged mode, first value to be set will be used everywhere (fixed value)
					// So we need to set the special ones before anything else
					array_unshift($cssFiles, 'css/root.funnel.css');
				else
					// In normal mode, new values replace the old (regular CSS)
					// Se we can set them wherever we want, provided it's after 'root.css'
					array_push($cssFiles, 'css/root.funnel.css');
			}

			$template->linkFiles($cssFiles);
		?>

		<!-- Social -->
		<?php require_once $template->getTemplate('page/include/opengraph'); ?>

		<!-- Analytics -->
		<?php
			if ($this->m_app->getAppMode() !== \Goji\Core\App::DEBUG)
				require_once $template->getTemplate('page/include/analytics');
		?>

		<!-- Scripts -->
		<?php require_once $template->getTemplate('page/include/head-javascript'); ?>
	</head>
	<body id="<?= $this->m_app->getRouter()->getCurrentPage(); ?>">
		<?php
			if ($template->getSpecial('is-funnel-page'))
				require_once $template->getTemplate('page/include/header.funnel');
			else
				require_once $template->getTemplate('page/include/header');
		?>

		<?= $template->getPageContent(); ?>

		<?php require_once $template->getTemplate('page/include/footer'); ?>

		<?php require_once $template->getTemplate('page/include/bottom-javascript'); ?>
	</body>
</html>
