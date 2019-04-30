<?php

	namespace App\Controller;

	use Goji\Core\App;
	use Goji\Blueprints\ControllerInterface;
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

			// Of course you don't need tu use SimpleTemplate.
			// You could also just include an entire html/php file (use readfile( *.html ))

			$template = new SimpleTemplate(TITLE_HOME,
			                                DESCRIPTION_HOME);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/home_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main_t.php';
		}
	}
