<?php

	namespace App\Model\HR;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputTextEmail;
	use Goji\Form\InputTextPassword;
	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;

	class LoginForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->setAction('xhr-login');

			$sanitizeEmail = function($email) {
				SwissKnife::sanitizeEmail($email);
				return $email;
			};

			$this->addClass('form__centered')
			     ->setId('login__form');

				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'login__email')
					 ->setAttribute('textContent', $tr->_('LOGIN_FORM_EMAIL'));
				$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					 ->setAttribute('name', 'login[email]')
					 ->setId('login__email')
					 ->setAttribute('placeholder', $tr->_('LOGIN_FORM_EMAIL_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'login__password')
					 ->setAttribute('textContent', $tr->_('LOGIN_FORM_PASSWORD'))
					 ->setSideInfo('a', ['href' => '#', 'id' => 'login__forgot-password'], $tr->_('LOGIN_FORGOT_PASSWORD'));
				$this->addInput(new InputTextPassword())
					 ->setAttribute('name', 'login[password]')
					 ->setId('login__password')
					 ->setAttribute('placeholder', $tr->_('LOGIN_FUN_MESSAGE', mt_rand(1, 3)))
					 ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
					 ->addClass('highlight loader')
					 ->setAttribute('textContent', $tr->_('LOGIN_FORM_LOG_IN_BUTTON'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
		}
	}
