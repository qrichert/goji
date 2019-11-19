<?php

	namespace App\Controller\HR;

	use App\Model\HR\SignUpForm;
	use Goji\Blueprints\CachedControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;

	class SignUpController extends CachedControllerAbstract {

		public function render(): void {

			// TODO: Impletement "didn't receive my email with the password"

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
