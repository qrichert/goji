<?php

	namespace App\Controller;

	use Goji\Blueprints\CachedControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;

	class PrivacyAndTermsController extends CachedControllerAbstract {

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$template = new SimpleTemplate($tr->_('PRIVACY_AND_TERMS_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
			                               $tr->_('PRIVACY_AND_TERMS_PAGE_DESCRIPTION'),
											SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('PrivacyAndTermsView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
