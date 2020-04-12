<?php

namespace HR\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Translation\Translator;
use HR\Model\MemberProfile;
use HR\Model\SettingsProfileForm;

class XhrSettingsProfileController extends XhrControllerAbstract {

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('SETTINGS_FORM_ERROR')
			], false);
		}

		$formDisplayName = $form->getInputByName('settings[display-name]')->getValue();

		$memberProfile = new MemberProfile($this->m_app, $this->m_app->getUser()->getId());
			$memberProfile->setDisplayName($formDisplayName);
			$memberProfile->save();

		HttpResponse::JSON([
			'message' => $tr->_('SETTINGS_FORM_SUCCESS')
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new SettingsProfileForm($tr);
			$form->hydrate();

		$this->treatForm($form);
	}
}
