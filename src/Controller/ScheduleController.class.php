<?php

	namespace App\Controller;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class ScheduleController extends ControllerAbstract {

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$template = new SimpleTemplate($tr->_('SCHEDULE_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('SCHEDULE_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('ScheduleView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
