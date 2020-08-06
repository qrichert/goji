<?php

namespace Blog\Model;

use Goji\Form\Form;
use Goji\Form\InputButtonElement;
use Goji\Form\InputCustom;
use Goji\Form\InputLabel;
use Goji\Form\InputNumber;
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

			// Submit
			$this->addInput(new InputButtonElement())
			     ->setId('blog-post__submit')
			     ->addClasses('highlight loader')
			     ->setAttribute('textContent', $tr->_('PUBLISH'));

			// Feedback
			$this->addInput(new InputCustom('<p class="form__success"></p>'));
			$this->addInput(new InputCustom('<p class="form__error"></p>'));

			// Tooltip
			$this->addInput(new InputCustom('
				<div class="tooltip left">
					<div class="tooltip__button"></div>
					<div class="tooltip__text">
						' . $tr->_('BLOG_POST_MARKDOWN_TOOLTIP') . '
					</div>
				</div>
			'));

			// More
			$this->addInput(new InputCustom('<a id="blog-post__more-options">' . $tr->_('BLOG_POST_LESS_OPTIONS') . '</a>'));

			// Permalink
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__permalink')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_PERMALINK'));
			$this->addInput(new InputText())
				 ->setName('blog-post[permalink]')
				 ->setId('blog-post__permalink')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_PERMALINK_PLACEHOLDER'));

			// Date
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__publication-date--year')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_DATE'));

			$this->addInput(new InputCustom('<div id="blog-post__publication-date">'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][year]')
					 ->setId('blog-post__publication-date--year')
					 ->setAttribute('min', 1995)
					 ->setAttribute('placeholder', date('Y'));

				$this->addInput(new InputCustom('<span>/</span>'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][month]')
					 ->setId('blog-post__publication-date--month')
					 ->setAttribute('max', 12)
					 ->setAttribute('min', 1)
					 ->setAttribute('placeholder', date('m'));

				$this->addInput(new InputCustom('<span>/</span>'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][day]')
					 ->setId('blog-post__publication-date--day')
					 ->setAttribute('max', 31)
					 ->setAttribute('min', 1)
					 ->setAttribute('placeholder', date('d'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][hours]')
					 ->setId('blog-post__publication-date--hours')
					 ->setAttribute('max', 23)
					 ->setAttribute('min', 0)
					 ->setAttribute('placeholder', date('H'));

				$this->addInput(new InputCustom('<span>:</span>'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][minutes]')
					 ->setId('blog-post__publication-date--minutes')
					 ->setAttribute('max', 59)
					 ->setAttribute('min', 0)
					 ->setAttribute('placeholder', date('i'));

				$this->addInput(new InputNumber())
					 ->setName('blog-post[publication-date][seconds]')
					 ->setId('blog-post__publication-date--seconds')
					 ->addClass('hidden')
					 ->setAttribute('max', 59)
					 ->setAttribute('min', 0)
					 ->setAttribute('placeholder', date('s'));

				$this->addInput(new InputCustom(<<<'EOT'
					<script>
						(function() {
							let pad = e => {
								let element = e.target;
								let value = '' + element.value;

								while (value.length < 2)
									value = '0' + value;

								element.value = value;
							};

							[
								'#blog-post__publication-date--month',
								'#blog-post__publication-date--day',
								'#blog-post__publication-date--hours',
								'#blog-post__publication-date--minutes',
								'#blog-post__publication-date--seconds'
							].forEach(el => document.querySelector(el).addEventListener('change', pad, false));
						})();
					</script>
					EOT));

			$this->addInput(new InputCustom('</div>'));

			// Illustration
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__illustration')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_ILLUSTRATION'));
			$this->addInput(new InputText(null, false, $sanitizeIllustration)) // Can't use URL because we allow web://img/img.jpg style links
				 ->setName('blog-post[illustration]')
				 ->setId('blog-post__illustration')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_ILLUSTRATION_PLACEHOLDER'));

			// Description
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__description')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_DESCRIPTION'));
			$this->addInput(new InputTextArea(null, false, $sanitizeDescription))
				 ->setName('blog-post[description]')
				 ->setId('blog-post__description')
				 ->addClasses('content-like')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_DESCRIPTION_PLACEHOLDER'));

			$this->addInput(new InputCustom(<<<EOT
				<script>
					(function() {
						let moreOptionsToggler = document.querySelector('#blog-post__more-options');
						let moreOptionsElements = [];
						let visible = !document.querySelector('#blog-post__permalink').classList.contains('hidden');

						[
							'label[for="blog-post__permalink"]',
							'#blog-post__permalink',
							'label[for="blog-post__publication-date--year"]',
							'#blog-post__publication-date',
							'label[for="blog-post__illustration"]',
							'#blog-post__illustration',
							'label[for="blog-post__description"]',
							'#blog-post__description'
						].forEach(el => {
							moreOptionsElements.push(document.querySelector(el));
						});

						let toggleMoreOptions = () => {
							moreOptionsElements.forEach(el => el.classList.toggle('hidden'));
							visible = !visible;

							moreOptionsToggler.textContent = visible ? '{$tr->_('BLOG_POST_LESS_OPTIONS')}' : '{$tr->_('BLOG_POST_MORE_OPTIONS')}';
						};

						if (visible)
							toggleMoreOptions();

						moreOptionsToggler.addEventListener('click', e => {
							e.preventDefault();
							toggleMoreOptions();
						}, false);
					})();
				</script>
				EOT));

			// Title
			$this->addInput(new InputLabel())
				 ->setAttribute('for', 'blog-post__title')
				 ->addClass('required')
				 ->setAttribute('textContent', $tr->_('BLOG_POST_TITLE'));
			$this->addInput(new InputTextArea())
				 ->setName('blog-post[title]')
				 ->setId('blog-post__title')
				 ->setAttribute('placeholder', $tr->_('BLOG_POST_TITLE_PLACEHOLDER'))
				 ->setAttribute('required');

			// Post
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

			// Progress
			$this->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
	}
}
