<?php

	namespace App\Model\HR;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputTextEmail;
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

			$this->setAttribute('class', 'form__centered')
				 ->setAttribute('id', 'reset-password__form');

				$this->addInput(new InputCustom('<p class="form__help-text">' . $tr->_('RESET_PASSWORD_HELP_TEXT') . '</p>'));
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'reset-password__email')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_EMAIL'));
				$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					 ->setAttribute('name', 'reset-password[email]')
					 ->setAttribute('id', 'reset-password__email')
					 ->setAttribute('placeholder', $tr->_('RESET_PASSWORD_FORM_EMAIL_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
					 ->setAttribute('class', 'highlight loader')
					 ->setAttribute('textContent', $tr->_('RESET_PASSWORD_FORM_RESET_BUTTON'));
				$this->addInput(new InputCustom('<p class="form__success"></p>'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
		}
	}
