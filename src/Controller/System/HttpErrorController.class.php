<?php

	namespace App\Controller\System;

	use Goji\Blueprints\HttpErrorControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;

	class HttpErrorController extends HttpErrorControllerAbstract {

		public function render(): void {

			/*********************/

			// Giving direct access in debug mode for testing
			// So you can just go to any page producing an error,
			// and add ?debug-error=403 for the 403 version for instance
			if ($this->m_app->getAppMode() === App::DEBUG
			    && isset($_GET['debug-error'])
			    && !empty($_GET['debug-error'])) {

				$this->m_httpErrorCode = (int) $_GET['debug-error'];
			}

			/*********************/

			if (!isset($this->m_httpErrorCode))
				$this->m_httpErrorCode = self::HTTP_ERROR_DEFAULT;

			// Set correct header
			HttpResponse::setStatusHeader($this->m_httpErrorCode);

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$template = new SimpleTemplate();
				$template->setPageTitle(str_replace('%{ERROR_CODE}', $this->m_httpErrorCode, $tr->_('ERROR_PAGE_TITLE')) . ' - ' . $this->m_app->getSiteName());
				$template->setPageDescription($tr->_('ERROR_PAGE_DESCRIPTION'));
				$template->setRobotsBehaviour(SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
				$template->setShowCanonicalPageAndAlternates(false);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('System/ErrorView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
