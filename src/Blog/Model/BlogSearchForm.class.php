<?php

namespace Blog\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputCheckBox;
use Goji\Form\InputCustom;
use Goji\Form\InputText;

class BlogSearchForm extends Form {

	function __construct(App $app) {

		parent::__construct();

		$tr = $app->getTranslator();
		$blogManager = new BlogManager($app);

		$this->setId('form__blog-search');

			$this->addInput(new InputText())
				 ->setName('blog-search[query]')
				 ->setId('blog-search__query')
				 ->setAttribute('placeholder', $tr->_('BLOG_SEARCH_BLOG_POST_PLACEHOLDER'));

		$categories = $blogManager->getCategories();

		if (!empty($categories)) {
			$this->addInput(new InputCustom('<div id="blog-search__categories">'));

			foreach ($categories as $category) {
				$categoryId = $category['id'];
				$categoryName = $category['name'];
				$this->addInput(new InputCheckBox())
				     ->setName("blog-search[category][$categoryId]")
				     ->setId("blog-search__category--$categoryId]")
				     ->addClass('squared')
				     ->setAttribute('textContent', $categoryName);
			}

			$this->addInput(new InputCustom('</div>'));
		}
	}
}
