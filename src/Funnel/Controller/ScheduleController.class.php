<?php

namespace Funnel\Controller;

use Goji\Blueprints\CachedControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class ScheduleController extends CachedControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$template = new SimpleTemplate($tr->_('SCHEDULE_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
		                               $tr->_('SCHEDULE_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-funnel-page', true);

		$template->startBuffer();

		// Getting the view (into buffer)
		require_once $template->getView('Funnel/ScheduleView');

		// Now the view is accessible as string w/ $template->getPageContent()
		$template->saveBuffer();

		// Inside the template file we call $template to put things in place.
		require_once $template->getTemplate('page/main');
	}
}
