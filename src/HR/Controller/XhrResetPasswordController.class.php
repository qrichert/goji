<?php

namespace HR\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\HumanResources\MemberManager;
use Goji\Translation\Translator;
use HR\Model\ResetPasswordForm;

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

		$formEmail = $form->getInputByName('reset-password[email]')->getValue();
		$formPassword = $form->getInputByName('reset-password[password]')->getValue();
		$formPasswordConfirmation = $form->getInputByName('reset-password[password-confirmation]')->getValue();
		$formToken = $form->getInputByName('reset-password[token]')->getValue();

		if (empty($formPassword) || $formPassword !== $formPasswordConfirmation) {

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_ERROR')
			], false);
		}

		if (!MemberManager::isValidResetPasswordRequest($this->m_app, $formToken, $formEmail, $memberId)) {

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_ERROR')
			], false);
		}

		// From here on the request is valid:

		if (!MemberManager::setNewPassword($this->m_app, (int) $memberId, $formPassword)) {

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_ERROR')
			], false);
		}

		// Here, password is up to date

		// Prevent reuse of token
		MemberManager::clearResetPasswordRequestForUser($this->m_app, $memberId);

		HttpResponse::JSON([
			'redirect_to' => $this->m_app->getRouter()->getLinkForPage('login') // Redirect to login
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
