<?php

namespace HR\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputTextPassword;
use Goji\Translation\Translator;

class SettingsPasswordForm extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$this->setAction('xhr-settings-password');

		$this->addClass('settings')
			 ->setId('settings__form--password');

		$this->addInput(new InputLabel())
		     ->setAttribute('for', 'settings__current-password')
		     ->setAttribute('textContent', $tr->_('SETTINGS_FORM_CURRENT_PASSWORD'));
		$this->addInput(new InputTextPassword())
		     ->setName('settings[current-password]')
		     ->setId('settings__current-password')
		     ->setAttribute('placeholder', $tr->_('SETTINGS_FORM_CURRENT_PASSWORD_PLACEHOLDER'))
		     ->setAttribute('required');

		$this->addInput(new InputLabel())
		     ->setAttribute('for', 'settings__password')
		     ->setAttribute('textContent', $tr->_('SETTINGS_FORM_PASSWORD'));
		$this->addInput(new InputTextPassword())
		     ->setName('settings[password]')
		     ->setId('settings__password')
		     ->setAttribute('placeholder', $tr->_('SETTINGS_FORM_PASSWORD_PLACEHOLDER'))
		     ->setAttribute('required');

		$this->addInput(new InputLabel())
		     ->setAttribute('for', 'settings__password-confirmation')
		     ->setAttribute('textContent', $tr->_('SETTINGS_FORM_PASSWORD_CONFIRMATION'));
		$this->addInput(new InputTextPassword())
		     ->setName('settings[password-confirmation]')
		     ->setId('settings__password-confirmation')
		     ->setAttribute('placeholder', $tr->_('SETTINGS_FORM_PASSWORD_CONFIRMATION_PLACEHOLDER'))
		     ->setAttribute('required');

		$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
		$this->addInput(new InputButtonElement())
		     ->addClass('highlight loader')
		     ->setAttribute('textContent', $tr->_('SAVE'));
		$this->addInput(new InputCustom('<p class="form__success"></p>'));
		$this->addInput(new InputCustom('<p class="form__error"></p>'));
	}
}
