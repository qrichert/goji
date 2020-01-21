<?php

namespace System\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputTextPassword;
use Goji\Translation\Translator;

class PasswordWallForm extends Form {

	public function __construct(Translator $tr, string $page) {

		parent::__construct();

		$this->setAction($page); // Current page

		$this->addClass('form__centered')
		     ->setId('password-wall__form');

			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'password-wall__password')
				 ->setAttribute('textContent', $tr->_('PASSWORD_WALL_FORM_PASSWORD'));
			$this->addInput(new InputTextPassword())
				 ->setName('password-wall[password]')
				 ->setId('password-wall__password')
				 ->setAttribute('placeholder', $tr->_('PASSWORD_WALL_FORM_PASSWORD_PLACEHOLDER'))
				 ->setAttribute('required');
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
			$this->addInput(new InputButtonElement())
				 ->addClass('highlight loader')
				 ->setAttribute('textContent', $tr->_('PASSWORD_WALL_FORM_ENTER_BUTTON'));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));
	}
}
