<?php

	namespace App\Controller;

	use Goji\Blueprints\CachedControllerAbstract;
	use Goji\Rendering\InPageContentEdit;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;

	class HomeController extends CachedControllerAbstract {

		public function render(): void {

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

			$inPageContentEdit = new InPageContentEdit($this->m_app, $this->m_app->getLanguages()->getCurrentCountryCode());

			$template = new SimpleTemplate($this->m_app->getSiteName(),
			                               $tr->_('HOME_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			// Like for controllers in config/routes.json5, this function is smart and will add the missing /View/ component
			// You just need to specify Module/FileName
			require_once $template->getView('App/HomeView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
