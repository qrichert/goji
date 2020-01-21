<?php

namespace HR\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\HumanResources\MemberManager;
use Goji\Translation\Translator;
use HR\Model\LoginForm;

class XhrLoginController extends XhrControllerAbstract {

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
			], false);
		}

		// Verify validity here (credentials validity)

		// User input
		$formUsername = $form->getInputByName('login[email]')->getValue();
		$formPassword = $form->getInputByName('login[password]')->getValue();

		$memberId = null; // Set by reference

		// If error return negative JSON response, unless it's a tmp user & we can log him in
		if (!MemberManager::isValidMember($this->m_app, $formUsername, $formPassword, $memberId)) {

			// If we couldn't log in, maybe the user is temporary

			// So we try to move him to the permanent user list, and log him in again
			if (!MemberManager::moveTemporaryMemberToPermanentList($this->m_app, $formUsername, $formPassword)
				|| !MemberManager::isValidMember($this->m_app, $formUsername, $formPassword, $memberId)) {

				// If it didn't work, we display an error
				HttpResponse::JSON([
					'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
				], false);
			}
		}

		// If we got here, credentials are valid -> SUCCESS -> log the user in

		$this->m_app->getUser()->logIn((int) $memberId);

		HttpResponse::JSON([
			'email' => $form->getInputByName('login[email]')->getValue(),
			'redirect_to' => $this->m_app->getAuthentication()->getRedirectToOnLogInSuccess()
		], true); // email, redirect_to, add status = SUCCESS
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new LoginForm($tr);
			$form->hydrate();

		$this->treatForm($form);
	}
}

