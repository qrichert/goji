<?php

	namespace App\Model\HR;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputTextEmail;
	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;

	class ResetPasswordRequestForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->setAction('xhr-reset-password-request');

			$sanitizeEmail = function($email) {
				SwissKnife::sanitizeEmail($email);
				return $email;
			};

			$this->addClass('form__centered')
				 ->setId('reset-password-request__form');

				$this->addInput(new InputCustom('<p class="form__help-text">' . $tr->_('RESET_PASSWORD_RQ_HELP_TEXT') . '</p>'));
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'reset-password-request__email')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_RQ_FORM_EMAIL'));
				$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					 ->setAttribute('name', 'reset-password[email]')
					 ->setId('reset-password-request__email')
					 ->setAttribute('placeholder', $tr->_('RESET_PASSWORD_RQ_FORM_EMAIL_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
					 ->addClass('highlight loader')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_RQ_FORM_RESET_BUTTON'));
				$this->addInput(new InputCustom('<p class="form__success"></p>'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
		}
	}
