<?php

	namespace Goji\Blog;

	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputText;
	use Goji\Form\InputTextArea;
	use Goji\Translation\Translator;

	/**
	 * Class BlogPostForm
	 *
	 * @package App\Model\Blog
	 */
	class BlogPostForm extends Form {

		function __construct(Translator $tr) {

			parent::__construct();

			$this->addClass('form__blog-post');

				$this->addInput(new InputCustom('<p class="form__success"></p>'));
				$this->addInput(new InputCustom('<p class="form__error"></p>'));
				$this->addInput(new InputLabel())
				     ->setAttribute('for', 'blog-post__permalink')
				     ->setAttribute('textContent', $tr->_('BLOG_POST_PERMALINK'));
				$this->addInput(new InputText())
					 ->setAttribute('name', 'blog-post[permalink]')
					 ->setId('blog-post__permalink')
					 ->setAttribute('placeholder', $tr->_('BLOG_POST_PERMALINK_PLACEHOLDER'));
				$this->addInput(new InputLabel())
				     ->setAttribute('for', 'blog-post__title')
					 ->addClass('required')
				     ->setAttribute('textContent', $tr->_('BLOG_POST_TITLE'));
				$this->addInput(new InputText())
				     ->setAttribute('name', 'blog-post[title]')
				     ->setId('blog-post__title')
				     ->setAttribute('placeholder', $tr->_('BLOG_POST_TITLE_PLACEHOLDER'))
					 ->setAttribute('required');
				$this->addInput(new InputLabel())
				     ->setAttribute('for', 'blog-post__post')
				     ->addClass('required')
				     ->setAttribute('textContent', $tr->_('BLOG_POST_POST'));
				$this->addInput(new InputTextArea())
				     ->setAttribute('name', 'blog-post[post]')
				     ->setId('blog-post__post')
				     ->addClass('big')
				     ->setAttribute('placeholder', $tr->_('BLOG_POST_POST_PLACEHOLDER'))
				     ->setAttribute('required');
				$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
				$this->addInput(new InputButtonElement())
				     ->addClass('highlight loader')
				     ->setAttribute('textContent', $tr->_('PUBLISH'));
		}
	}
