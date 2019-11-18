<?php

	namespace App\Model;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputText;
	use Goji\Form\InputTextArea;
	use Goji\Form\InputTextEmail;
	use Goji\Translation\Translator;

	class ContactForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->setAction('xhr-contact');

			$sanitizeEmail = function($email) {
				$email = mb_strtolower($email);
				return filter_var($email, FILTER_SANITIZE_EMAIL);
			};

			$this->addClass('form__contact');

				$this->addInput(new InputCustom('<p class="form__success"></p>'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'contact__name')
					 ->setAttribute('textContent', $tr->_('CONTACT_FORM_NAME'));
				$this->addInput(new InputText())
					 ->setAttribute('name', 'contact[name]')
					 ->setId('contact__name')
					 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_NAME_PLACEHOLDER'));
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'contact__email')
					 ->setAttribute('textContent', $tr->_('CONTACT_FORM_EMAIL'));
				$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					 ->setAttribute('name', 'contact[email]')
					 ->setId('contact__email')
					 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_EMAIL_PLACEHOLDER'));
				$this->addInput(new InputLabel())
					 ->setAttribute('for', 'contact__message')
					 ->addClass('required')
					 ->setAttribute('textContent', $tr->_('CONTACT_FORM_MESSAGE'));
				$this->addInput(new InputTextArea())
					 ->setAttribute('name', 'contact[message]')
					 ->setId('contact__message')
					 ->addClass('big')
					 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_MESSAGE_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
					 ->addClass('highlight loader')
					 ->setAttribute('textContent', $tr->_('SEND'));
		}
	}
