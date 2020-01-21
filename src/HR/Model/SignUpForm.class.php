<?php

namespace HR\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputTextEmail;
use Goji\Toolkit\SwissKnife;
use Goji\Translation\Translator;

class SignUpForm extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$this->setAction('xhr-sign-up');

		$sanitizeEmail = function($email) {
			SwissKnife::sanitizeEmail($email);
			return $email;
		};

		$this->addClass('form__centered')
			 ->setId('sign-up__form');

			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'sign-up__email')
			     ->setAttribute('textContent', $tr->_('SIGN_UP_FORM_EMAIL'));
			$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
			     ->setName('sign-up[email]')
			     ->setId('sign-up__email')
			     ->setAttribute('placeholder', $tr->_('SIGN_UP_FORM_EMAIL_PLACEHOLDER'))
			     ->setAttribute('required');
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
			$this->addInput(new InputButtonElement())
			     ->addClass('highlight loader')
			     ->setAttribute('textContent', $tr->_('SIGN_UP_FORM_SIGN_UP_BUTTON'));
			$this->addInput(new InputCustom('<p class="form__success"></p>'));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));
	}
}
