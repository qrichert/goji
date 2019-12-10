<?php

	namespace HR\Controller;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\App;
	use Goji\HumanResources\MemberManager;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;
	use HR\Model\ResetPasswordForm;

	class ResetPasswordController extends ControllerAbstract {

		/* <ATTRIBUTES> */

		private $m_token;

		public function __construct(App $app) {

			parent::__construct($app);

			if (empty($_GET['token']))
				$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);

			$this->m_token = (string) $_GET['token'];

			// Check if given token exists
			if (!MemberManager::isValidResetPasswordRequest($this->m_app, $this->m_token))
				$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$resetPasswordForm = new ResetPasswordForm($tr);
				$resetPasswordForm->getInputByName('reset-password[token]')->setValue($this->m_token);

			$template = new SimpleTemplate($tr->_('RESET_PASSWORD_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
			                               $tr->_('RESET_PASSWORD_PAGE_DESCRIPTION'),
			                               SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('HR/ResetPasswordView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
