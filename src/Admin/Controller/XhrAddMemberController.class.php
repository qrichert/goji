<?php

namespace Admin\Controller;

use Admin\Model\AddMemberForm;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\HumanResources\MemberManager;
use Goji\Translation\Translator;

class XhrAddMemberController extends XhrControllerAbstract {

	private function treatForm(Form $form) {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR')
			], false);
		}

		$formEmail = $form->getInputByName('add-member[email]')->getValue();
		$formPassword = $form->getInputByName('add-member[password]')->getValue();
		$formPasswordConfirmation = $form->getInputByName('add-member[password-confirmation]')->getValue();
		$formRole = $form->getInputByName('add-member[role]')->getValue();

		if (empty($formPassword) || $formPassword !== $formPasswordConfirmation) {
			HttpResponse::JSON([
				'message' => $tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR_PASSWORDS_MUST_MATCH')
			], false);
		}

		if (!array_key_exists($formRole, $this->m_app->getMemberManager()->getRolesAvailable())) {
			HttpResponse::JSON([
				'message' => $tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR')
			], false);
		}

		// Convert textual role to numeric weight
		$formRole = $this->m_app->getMemberManager()->getRolesAvailable()[$formRole];

		// Create member
		$detail = [];

		if (!MemberManager::createMember($this->m_app, $formEmail, $formPassword, $formRole, $detail)) {

			$message = '';

			if (!empty($detail['error']) && $detail['error'] == MemberManager::E_MEMBER_ALREADY_EXISTS)
				$message = $tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR_USERNAME_ALREADY_EXISTS');
			else
				$message = $tr->_('ADMIN_ACTION_ADD_MEMBER_ERROR');

			HttpResponse::JSON([
				'message' => $message
			], false);
		}

		HttpResponse::JSON([
			'message' => $tr->_('ADMIN_ACTION_ADD_MEMBER_SUCCESS')
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
		$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new AddMemberForm($tr, $this->m_app);
		$form->hydrate();

		$this->treatForm($form);
	}
}
