<?php

	namespace App\Controller\HR;

	use App\Model\HR\LoginForm;
	use App\Model\HR\ResetPasswordForm;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class LoginController extends ControllerAbstract {

		public function render(): void {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');
				$tr->loadTranslationResource('%{LOCALE}.tr.xml', false, 'xhr-reset-password');

			$form = new LoginForm($tr);
			$resetPasswordForm = new ResetPasswordForm($tr);

			$template = new SimpleTemplate($tr->_('LOGIN_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('LOGIN_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('HR/LoginView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
