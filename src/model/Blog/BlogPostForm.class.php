<?php

	namespace App\Model\Blog;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputText;
	use Goji\Form\InputTextArea;
	use Goji\Translation\Translator;

	class BlogPostForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->setAttribute('class', 'form__blog-post');

				$this->addInput(new InputLabel())
				     ->setAttribute('for', 'blog-post__title')
					 ->setAttribute('class', 'required')
				     ->setAttribute('textContent', $tr->_('BLOG_POST_TITLE'));
				$this->addInput(new InputText())
				     ->setAttribute('name', 'blog-post[title]')
				     ->setAttribute('id', 'blog-post__title')
				     ->setAttribute('placeholder', $tr->_('BLOG_POST_TITLE_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputLabel())
				     ->setAttribute('for', 'blog-post__post')
				     ->setAttribute('class', 'required')
				     ->setAttribute('textContent', $tr->_('BLOG_POST_POST'));
				$this->addInput(new InputTextArea())
				     ->setAttribute('name', 'blog-post[post]')
				     ->setAttribute('id', 'blog-post__post')
				     ->setAttribute('class', 'big')
				     ->setAttribute('placeholder', $tr->_('BLOG_POST_POST_PLACEHOLDER'))
				     ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
				     ->setAttribute('class', 'highlight loader')
				     ->setAttribute('textContent', $tr->_('PUBLISH'));
		}
	}
