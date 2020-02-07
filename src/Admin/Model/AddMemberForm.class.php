<?php

namespace Admin\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputSelect;
use Goji\Form\InputSelectOption;
use Goji\Form\InputTextEmail;
use Goji\Form\InputTextPassword;
use Goji\Toolkit\SwissKnife;
use Goji\Translation\Translator;

class AddMemberForm extends Form {

	function __construct(Translator $tr, App $app) {

		parent::__construct();

		$rolesAvailable = $app->getMemberManager()->getRolesAvailable();

		$this->setAction($app->getRouter()->getLinkForPage('xhr-admin-add-member'));

		$sanitizeEmail = function($email) {
			SwissKnife::sanitizeEmail($email);
			return $email;
		};

		$this->addClass('form__centered')
		     ->setId('admin-action__add-member--form');

			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'add-member__email')
			     ->setAttribute('textContent', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_EMAIL'));
			$this->addInput(new InputTextEmail(null, false, $sanitizeEmail))
			     ->setName('add-member[email]')
			     ->setId('add-member__email')
			     ->setAttribute('placeholder', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_EMAIL_PLACEHOLDER'))
			     ->setAttribute('required');

			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'add-member__password')
			     ->setAttribute('textContent', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_PASSWORD'));
			$this->addInput(new InputTextPassword())
			     ->setName('add-member[password]')
			     ->setId('add-member__password')
			     ->setAttribute('placeholder', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_PASSWORD_PLACEHOLDER'))
			     ->setAttribute('required');

			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'add-member__password-confirmation')
			     ->setAttribute('textContent', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_PASSWORD_CONFIRMATION'));
			$this->addInput(new InputTextPassword())
			     ->setName('add-member[password-confirmation]')
			     ->setId('add-member__password-confirmation')
			     ->setAttribute('placeholder', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_PASSWORD_CONFIRMATION_PLACEHOLDER'))
			     ->setAttribute('required');

			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'add-member__role')
			     ->setAttribute('textContent', $tr->_('ADMIN_ACTION_ADD_MEMBER_FORM_ROLE'));
			$inputSelectRole = new InputSelect();

				 $this->addInput($inputSelectRole);
				 $inputSelectRole->setName('add-member[role]')
								 ->setId('add-member__role')
								 ->setAttribute('required');

				 foreach ($rolesAvailable as $role => $description) {

				 	 $description = $tr->_('ROLE', $description);

					 $inputSelectRole->addOption(new InputSelectOption())
					                 ->setAttribute('value', $role)
					                 ->setAttribute('textContent', $description);
				 }

			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
			$this->addInput(new InputButtonElement())
			     ->addClass('highlight loader')
			     ->setAttribute('textContent', $tr->_('CREATE'));
			$this->addInput(new InputCustom('<p class="form__success"></p>'));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));
	}
}
