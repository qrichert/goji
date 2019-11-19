<?php

	namespace App\Controller;

	use Goji\Blueprints\CachedControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class LandingController extends CachedControllerAbstract {

		public function render(): void {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				// Will be 'en_US.tr.xml' or 'fr.tr.xml', etc.
				// If you have a file called 'en.tr.xml' it will match for both
				// en_US & en_GB because the language code is the same (en)
				// You can send an array of files as well.
				// If you use PHP constants for example:
				//$tr->loadTranslationResource('%{LOCALE}.tr.php');
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			// Of course you don't need tu use SimpleTemplate.
			// You could also just include an entire html/php file (use readfile( *.html ))

			$template = new SimpleTemplate($tr->_('LANDING_PAGE_PAGE_TITLE'),
			                                $tr->_('LANDING_PAGE_PAGE_DESCRIPTION'));
				$template->addSpecial('is-funnel-page', true);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('LandingPageView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
