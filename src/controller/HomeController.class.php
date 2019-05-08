<?php

	namespace App\Controller;

	use Goji\Core\App;
	use Goji\Blueprints\ControllerInterface;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class HomeController implements ControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				// Will be 'en_US.tr.php' or 'fr.tr.php', etc.
				// If you have a file called 'en.tr.php' it will match for both
				// en_US & en_GB because the language code is the same (en)
				// You can send an array of files as well.
				//$tr->loadTranslationResource('%{LOCALE}.tr.php');
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			// Of course you don't need tu use SimpleTemplate.
			// You could also just include an entire html/php file (use readfile( *.html ))

			$template = new SimpleTemplate($tr->_('HOME_PAGE_TITLE'),
			                                $tr->_('HOME_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/home_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main_t.php';
		}
	}
