<?php

	namespace App\Controller\HR;

	use App\Model\HR\ResetPasswordForm;
	use Exception;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Form\Form;
	use Goji\HumanResources\MemberManager;
	use Goji\Security\Passwords;
	use Goji\Toolkit\Mail;
	use Goji\Translation\Translator;

	class XhrResetPasswordController extends XhrControllerAbstract {

		private function treatForm(Form $form): void {

			$tr = $this->m_app->getTranslator();

			$detail = [];

			if (!$form->isValid($detail)) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('RESET_PASSWORD_ERROR')
				], false);
			}

			// Verify validity here (credentials validity)

			// User input
			$formUsername = $form->getInputByName('reset-password[email]')->getValue();

			$token = '';

			try {

				$token = MemberManager::queueResetPasswordRequest($this->m_app, $formUsername);

			} catch (Exception $e) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('RESET_PASSWORD_ERROR')
				], false);
			}

			$link = $this->m_app->getRouter()->getLinkForPage(null, null, true);
				$link .= '?token=' . $token;

			// Send Mail
			$message = $tr->_('RESET_PASSWORD_EMAIL_MESSAGE');
				$message = str_replace('%{LINK}', htmlspecialchars($link), $message);

			$options = [
				'site_url' => $this->m_app->getSiteUrl(),
				'site_name' => $this->m_app->getSiteName(),
				'site_domain_name' => $this->m_app->getSiteDomainName(),
				'company_email' => $this->m_app->getCompanyEmail()
			];

			Mail::sendMail($formUsername, $tr->_('RESET_PASSWORD_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_SUCCESS')
			], true);
		}

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new ResetPasswordForm($tr);
				$form->hydrate();

			$this->treatForm($form);
		}
	}
