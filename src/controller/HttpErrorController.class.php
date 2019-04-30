<?php

	namespace App\Controller;

	use Goji\Blueprints\HttpErrorControllerAbstract;
	use Goji\Core\App;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class HttpErrorController extends HttpErrorControllerAbstract {

		/* <ATTRIBUTES> */

		private $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		public function render() {

			/*********************/

			// Giving direct access in debug mode for testing
			// So you can just go to any page producing an error,
			// and add ?debug-error=403 for the 403 version for instance
			if ($this->m_app->getAppMode() === App::DEBUG
			    && isset($_GET['debug-error'])
			    && !empty($_GET['debug-error'])) {

				$this->m_httpErrorCode = intval($_GET['debug-error']);
			}

			/*********************/

			if (!isset($this->m_httpErrorCode))
				$this->m_httpErrorCode = self::HTTP_ERROR_DEFAULT;

			switch ($this->m_httpErrorCode) {
				// header('(HTTP/1.0|HTTP/1.1) ERROR DESCRIPTION', true (replace = default), RESPONSE CODE);
				case self::HTTP_ERROR_FORBIDDEN:	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);				break;
				case self::HTTP_ERROR_NOT_FOUND:	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);				break;
				case self::HTTP_SERVER_INTERNAL_SERVER_ERROR:	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);	break;
			}

			// If it's a 404 for instance, Router can't have a page set
			if (!$this->m_app->getRouter()->hasCurrentPage())
				$this->m_app->getRouter()->setCurrentPage('http-error-' . $this->m_httpErrorCode);

			SimpleMetrics::addPageView('http-error-' . $this->m_httpErrorCode);

			$template = new SimpleTemplate();
				$template->setPageTitle(TITLE_ERROR . ' ' . $this->m_httpErrorCode);
				$template->setPageDescription(DESCRIPTION_ERROR);
				$template->setRobotsBehaviour(SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
				$template->setShowCanonicalPageAndAlternates(false);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/error_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main_t.php';
		}
	}
