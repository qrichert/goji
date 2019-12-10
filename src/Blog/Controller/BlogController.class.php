<?php

	namespace Blog\Controller;

	use Blog\Resource\BlogPostTrait;
	use Blog\Model\BlogPostManager;
	use Goji\Core\App;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;

	class BlogController extends BlogControllerAbstract {

		use BlogPostTrait;

		/* <CONSTANTS> */

		const BLOG_POST_PREVIEW_MAX_LENGTH = 250;

		public function __construct(App $app) {

			parent::__construct($app);

			$this->activateCacheIfRolePermits();
		}

		public static function renderCleanAndCut($content) {

			$content = self::renderClean($content);

			if (mb_strlen($content) > self::BLOG_POST_PREVIEW_MAX_LENGTH)
				$content = SwissKnife::ceil_str($content, self::BLOG_POST_PREVIEW_MAX_LENGTH) . '...';

			return $content;
		}

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this);
			$blogPosts = $blogPostManager->getBlogPosts(0,
			                                            -1,
			                                            $this->m_app->getLanguages()->getCurrentCountryCode(),
			                                            [self::class, 'renderCleanAndCut']);

			$template = new SimpleTemplate($tr->_('BLOG_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
			                               $tr->_('BLOG_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('Blog/BlogView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
