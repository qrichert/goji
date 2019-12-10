<?php

	namespace HR\Controller;

	use Goji\Blueprints\CachedControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;
	use HR\Model\LoginForm;
	use HR\Model\ResetPasswordRequestForm;

	class LoginController extends CachedControllerAbstract {

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');
				$tr->loadTranslationResource('%{LOCALE}.tr.xml', false, 'xhr-reset-password-request');

			$form = new LoginForm($tr);
			$resetPasswordRequestForm = new ResetPasswordRequestForm($tr);

			$template = new SimpleTemplate($tr->_('LOGIN_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('LOGIN_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('HR/LoginView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
