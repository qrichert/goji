<?php

	namespace App\Controller\HR;

	use App\Model\HR\ResetPasswordForm;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Form\Form;
	use Goji\HumanResources\MemberManager;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
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

			$detail = [];

			if (!MemberManager::resetPassword($this->m_app, $formUsername, $detail)) {

				//if ($detail['error'] == MemberManager::E_MEMBER_DOES_NOT_EXIST) {

					HttpResponse::JSON([
						'message' => $tr->_('RESET_PASSWORD_ERROR')
					], false);
				//}
			}

			// Send Mail
			$message = $tr->_('RESET_PASSWORD_EMAIL_MESSAGE');
				$message = str_replace('%{PASSWORD}', htmlspecialchars($detail['password']), $message);

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

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new ResetPasswordForm($tr);
				$form->hydrate();

			$this->treatForm($form);
		}
	}
