<?php

	namespace App\Controller;

	use App\Model\ContactForm;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class ContactController extends ControllerAbstract {

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			// Translation
			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			// Form
			$form = new ContactForm($tr);

			// Template
			$template = new SimpleTemplate($tr->_('CONTACT_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('CONTACT_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('ContactView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
