<?php

namespace Blog\Model;

use Goji\Form\Form;
use Goji\Form\InputText;
use Goji\Translation\Translator;

class BlogSearchForm extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$this->setId('form__blog-search');

			$this->addInput(new InputText())
				 ->setName('blog-search[query]')
				 ->setId('blog-search__query')
				 ->setAttribute('placeholder', $tr->_('BLOG_SEARCH_BLOG_POST_PLACEHOLDER'))
				 ->setAttribute('required');
	}
}
