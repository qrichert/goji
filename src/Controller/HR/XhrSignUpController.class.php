<?php

	namespace App\Controller\HR;

	use App\Model\HR\SignUpForm;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\HumanResources\MemberManager;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrSignUpController extends XhrControllerAbstract {

		private function treatForm(Form $form): void {

			$tr = $this->m_app->getTranslator();

			$detail = [];

			// If form is not valid, in the sense that required info isn't there, email isn't an email, etc.
			if (!$form->isValid($detail)) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
				], false);
			}

			// Verify validity here (credentials validity)

			// User input
			$formUsername = $form->getInputByName('sign-up[email]')->getValue();

			$detail = [];

			if (!MemberManager::createMember($this->m_app, $formUsername, $detail)) {

				//if ($detail['error'] == MemberManager::E_MEMBER_ALREADY_EXISTS) {

					HttpResponse::JSON([
						'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
					], false);
				//}
			}

			// Send Mail
			$message = $tr->_('SIGN_UP_EMAIL_MESSAGE');
				$message = str_replace('%{PASSWORD}', htmlspecialchars($detail['password']), $message);

			$options = [
				'site_url' => $this->m_app->getSiteUrl(),
				'site_name' => $this->m_app->getSiteName(),
				'site_domain_name' => $this->m_app->getSiteDomainName(),
				'company_email' => $this->m_app->getCompanyEmail()
			];

			Mail::sendMail($formUsername, $tr->_('SIGN_UP_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

			HttpResponse::JSON([
				'message' => $tr->_('SIGN_UP_SUCCESS')
			], true);
		}

		public function render(): void {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new SignUpForm($tr);
				$form->hydrate();

			$this->treatForm($form);
		}
	}
