<?php

	namespace App\Controller\HR;

	use App\Model\HR\SignUpForm;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class SignUpController extends ControllerAbstract {

		public function render() {

			// TODO: Impletement "didn't receive my email with the password"

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new SignUpForm($tr);

			$template = new SimpleTemplate($tr->_('SIGN_UP_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('SIGN_UP_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('HR/SignUpView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
