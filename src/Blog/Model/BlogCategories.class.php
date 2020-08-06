<?php

namespace Blog\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputTextArea;
use Goji\Translation\Translator;

class BlogCategories extends Form {

	function __construct(Translator $tr, App $app) {

		parent::__construct();

		$this->setAction($app->getRouter()->getLinkForPage('xhr-admin-blog-categories'));
		$this->setId('form__blog-categories');

			$this->addInput(new InputCustom($tr->_('BLOG_CATEGORIES_FORM_HELP_TEXT')));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));
			$this->addInput(new InputTextArea())
				 ->setName('blog-categories[categories]')
				 ->setId('blog-categories__categories')
				 ->setAttribute('placeholder', $tr->_('BLOG_CATEGORIES_FORM_CATEGORIES_PLACEHOLDER'))
				 ->setAttribute('autofocus');
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
			$this->addInput(new InputButtonElement())
			     ->addClass('highlight loader')
			     ->setAttribute('textContent', $tr->_('SAVE'));
	}
}
