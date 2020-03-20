<?php

namespace Blog\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputText;
use Goji\Form\InputTextArea;
use Goji\Parsing\RegexPatterns;
use Goji\Translation\Translator;

/**
 * Class BlogPostForm
 *
 * @package App\Model\Blog
 */
class BlogPostForm extends Form {

	function __construct(Translator $tr) {

		parent::__construct();

		$sanitizeIllustration = function($illustration) {
			return trim($illustration);
		};

		$sanitizeDescription = function($description) {
			$description = preg_replace(RegexPatterns::whiteSpace(), ' ', $description);
			return trim($description);
		};

		$this->addClass('form__blog-post');

			$this->addInput(new InputButtonElement())
			     ->setId('blog-post__submit')
			     ->addClasses('highlight loader')
			     ->setAttribute('textContent', $tr->_('PUBLISH'));
			$this->addInput(new InputCustom('<p class="form__success"></p>'));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));
			$this->addInput(new InputCustom('
				<div class="tooltip left">
					<div class="tooltip__button"></div>
					<div class="tooltip__text">
						' . $tr->_('BLOG_POST_MARKDOWN_TOOLTIP') . '
					</div>
				</div>
			'));
			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'blog-post__permalink')
			     ->setAttribute('textContent', $tr->_('BLOG_POST_PERMALINK'));
			$this->addInput(new InputText())
				 ->setName('blog-post[permalink]')
				 ->setId('blog-post__permalink')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_PERMALINK_PLACEHOLDER'));
			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'blog-post__illustration')
			     ->setAttribute('textContent', $tr->_('BLOG_POST_ILLUSTRATION'));
			$this->addInput(new InputText(null, false, $sanitizeIllustration)) // Can't use URL because we allow %{WEBROOT}/img/img.jpg style links
			     ->setName('blog-post[illustration]')
			     ->setId('blog-post__illustration')
			     ->setAttribute('placeholder', $tr->_('BLOG_POST_ILLUSTRATION_PLACEHOLDER'));
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__description')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_DESCRIPTION'));
			$this->addInput(new InputTextArea(null, false, $sanitizeDescription))
				 ->setName('blog-post[description]')
				 ->setId('blog-post__description')
				 ->addClasses('content-like')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_DESCRIPTION_PLACEHOLDER'));
			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'blog-post__title')
				 ->addClass('required')
			     ->setAttribute('textContent', $tr->_('BLOG_POST_TITLE'));
			$this->addInput(new InputText())
			     ->setName('blog-post[title]')
			     ->setId('blog-post__title')
			     ->setAttribute('placeholder', $tr->_('BLOG_POST_TITLE_PLACEHOLDER'))
				 ->setAttribute('required');
			$this->addInput(new InputLabel())
			     ->setAttribute('for', 'blog-post__post')
			     ->addClass('required')
			     ->setAttribute('textContent', $tr->_('BLOG_POST_POST'));
			$this->addInput(new InputTextArea())
			     ->setName('blog-post[post]')
			     ->setId('blog-post__post')
			     ->addClasses('big content-like')
			     ->setAttribute('placeholder', $tr->_('BLOG_POST_POST_PLACEHOLDER'))
			     ->setAttribute('required');
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
	}
}
