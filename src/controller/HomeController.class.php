<?php

	namespace App\Controller;

	use Goji\Core\App;
	use Goji\Design\ControllerInterface;
	use Goji\Toolkit\SimpleTemplate;

	class HomeController implements ControllerInterface {

		private $m_app;

		public function __construct(App $app) {
			// TODO: App needs to contain lang
			// either set it in Router (according to config) or get it from Language class if not set
			$this->m_app = $app;

			// Form data goes here:
			// if (isset($_POST[''])) ...
		}

		public function render() {echo 'render home';return;

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
