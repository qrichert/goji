<?php

namespace HR\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Translation\Translator;
use HR\Model\SettingsPasswordForm;

class XhrSettingsPasswordController extends XhrControllerAbstract {

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('SETTINGS_FORM_ERROR')
			], false);
		}

		$formCurrentPassword = $form->getInputByName('settings[current-password]')->getValue();
		$formPassword = $form->getInputByName('settings[password]')->getValue();
		$formPasswordConfirmation = $form->getInputByName('settings[password-confirmation]')->getValue();

		// New passwords match
		if (empty($formPassword) || $formPassword !== $formPasswordConfirmation) {
			HttpResponse::JSON([
				'message' => $tr->_('SETTINGS_FORM_ERROR_PASSWORDS_MUST_MATCH')
			], false);
		}

		// Current password is valid
		if (!$this->m_app->getMemberManager()->getPasswordIsValid($formCurrentPassword)) {
			HttpResponse::JSON([
				'message' => $tr->_('SETTINGS_FORM_ERROR_WRONG_PASSWORD')
			], false);
		}

		// Password empty() or db error
		if (!$this->m_app->getMemberManager()->setPassword($formPassword)) {
			HttpResponse::JSON([
				'message' => $tr->_('SETTINGS_FORM_ERROR')
			], false);
		}

		HttpResponse::JSON([
			'message' => $tr->_('SETTINGS_FORM_SUCCESS_PASSWORD_CHANGED')
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new SettingsPasswordForm($tr);
			$form->hydrate();

		$this->treatForm($form);
	}
}
