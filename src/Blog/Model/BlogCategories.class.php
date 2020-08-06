<?php

namespace Blog\Model;

use Goji\Form\Form;
use Goji\Form\InputCustom;
use Goji\Form\InputTextArea;
use Goji\Translation\Translator;

class BlogCategories extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$this->setId('form__blog-categories');

			$this->addInput(new InputCustom($tr->_('BLOG_CATEGORIES_FORM_HELP_TEXT')));

			$this->addInput(new InputTextArea())
				 ->setName('blog-categories[categories]')
				 ->setId('blog-categories__categories')
				 ->setAttribute('placeholder', $tr->_('BLOG_CATEGORIES_FORM_CATEGORIES_PLACEHOLDER'))
				 ->setAttribute('required')
				 ->setAttribute('autofocus');
	}
}
