<?php

namespace Blog\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputHidden;
use Goji\Translation\Translator;

class BlogCategories extends Form {

	function __construct(Translator $tr, App $app) {

		parent::__construct();

		$this->setAction($app->getRouter()->getLinkForPage('xhr-admin-blog-categories'));

		$this->addClass('settings')
			 ->setId('form__blog-categories');

			$this->addInput(new InputCustom('<p class="form__error"></p>'));
			$this->addInput(new InputCustom('<div id="blog-categories__interface"></div>'));
			$this->addInput(new InputCustom('<a id="blog-categories__add-category">+ ' . $tr->_('BLOG_CATEGORIES_FORM_NEW_CATEGORY') . '</a>'));
			$this->addInput(new InputHidden())
				 ->setId('blog-categories__categories');
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
			$this->addInput(new InputButtonElement())
			     ->addClass('highlight loader')
			     ->setAttribute('textContent', $tr->_('SAVE'));
	}
}
