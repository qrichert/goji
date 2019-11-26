<?php

	namespace App\Model\HR;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputHidden;
	use Goji\Form\InputLabel;
	use Goji\Form\InputTextEmail;
	use Goji\Form\InputTextPassword;
	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;

	class ResetPasswordForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->setAction('xhr-reset-password');

			$sanitizeEmail = function($email) {
				SwissKnife::sanitizeEmail($email);
				return $email;
			};

			$this->addClass('form__centered')
				 ->setId('reset-password__form');

				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'reset-password__email')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_EMAIL'));
				$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					 ->setName('reset-password[email]')
					 ->setId('reset-password__email')
					 ->setAttribute('placeholder', $tr->_('RESET_PASSWORD_FORM_EMAIL_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'reset-password__password')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_PASSWORD'));
				$this->addInput(new InputTextPassword())
					 ->setName('reset-password[password]')
					 ->setId('reset-password__password')
					 ->setAttribute('placeholder', $tr->_('RESET_PASSWORD_FORM_PASSWORD_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'reset-password__password-confirmation')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_PASSWORD_CONFIRMATION'));
				$this->addInput(new InputTextPassword())
					 ->setName('reset-password[password-confirmation]')
					 ->setId('reset-password__password-confirmation')
					 ->setAttribute('placeholder', $tr->_('RESET_PASSWORD_FORM_PASSWORD_CONFIRMATION_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputHidden())
					 ->setName('reset-password[token]')
					 ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
					 ->addClass('highlight loader')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_RESET_BUTTON'));
				$this->addInput(new InputCustom('<p class="form__success"></p>'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
		}
	}
