<?php

	namespace App\Controller\HR;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\HumanResources\MemberManager;
	use Goji\Toolkit\Mail;
	use Goji\Translation\Translator;

	class XhrVerifyEmailController extends XhrControllerAbstract {

		/* <ATTRIBUTES> */

		private $m_id;
		private $m_token;
		private $m_email;

		public function __construct(App $app) {

			parent::__construct($app);

			// If no ID or token -> error
			if (empty($_POST['id']) || empty($_POST['token']))
				$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_FORBIDDEN);

			$this->m_id = (int) $_POST['id'];
			$this->m_token = (string) $_POST['token'];

			$this->m_email = MemberManager::getTemporaryMemberEmail($this->m_app, $this->m_id, $this->m_token);

			// If email not found (null), so incorrect id/token (token must match id)
			if (empty($this->m_email))
				$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_FORBIDDEN);
		}

		/**
		 * This is actually the 'Re-send email if not email confirmation received'
		 * So we reset the password (since we can't unhash it to send it back) and send it in a new email
		 *
		 * TODO: Rename this class to XhrVerifyEmailNotReceived.class.php
		 *
		 * @throws \Exception
		 */
		public function render(): void {

			// If we're here, id/token was valid and we got an email

			$detail = [];

			if (!MemberManager::resetPassword($this->m_app, $this->m_email, $detail)) {
				// Shouln't happend since it's verified already, but you never know
				HttpResponse::JSON([], false);
			}

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml', false, 'xhr-sign-up');

			// Send Mail
			$message = $tr->_('SIGN_UP_EMAIL_MESSAGE');
				$message = str_replace('%{PASSWORD}', htmlspecialchars($detail['password']), $message);

			$options = [
				'site_url' => $this->m_app->getSiteUrl(),
				'site_name' => $this->m_app->getSiteName(),
				'site_domain_name' => $this->m_app->getSiteDomainName(),
				'company_email' => $this->m_app->getCompanyEmail()
			];

			Mail::sendMail($this->m_email, $tr->_('SIGN_UP_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

			HttpResponse::JSON([], true);
		}
	}
