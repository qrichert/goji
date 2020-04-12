<?php

namespace HR\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputText;
use Goji\Translation\Translator;

class SettingsProfileForm extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$this->setAction('xhr-settings-profile');

		$this->addClass('settings')
		     ->setId('settings__form--profile');

		$this->addInput(new InputLabel())
		     ->setAttribute('for', 'settings__display-name')
		     ->setAttribute('textContent', $tr->_('SETTINGS_FORM_DISPLAY_NAME'));
		$this->addInput(new InputText())
		     ->setName('settings[display-name]')
		     ->setId('settings__display-name')
		     ->setAttribute('placeholder', $tr->_('ANONYMOUS'))
			 ->setAttribute('maxlength', 30);

		$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
		$this->addInput(new InputButtonElement())
		     ->addClass('highlight loader')
		     ->setAttribute('textContent', $tr->_('SAVE'));
		$this->addInput(new InputCustom('<p class="form__success"></p>'));
		$this->addInput(new InputCustom('<p class="form__error"></p>'));
	}
}
