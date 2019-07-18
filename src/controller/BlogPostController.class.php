<?php

	namespace App\Controller;

	use Goji\Blog\BlogPostControllerInterface;
	use Goji\Blog\BlogPostManager;
	use Goji\Core\App;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class BlogPostController implements BlogPostControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_id;

		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_id = $this->m_app->getRequestHandler()->getRequestParameters()[1] ?? null; // 0 = full match

			if (empty($this->m_id))
				$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('blog'));

			// Bad ID handled in BlogPostManager::read(); -> 404
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this, $tr);
			$blogPost = $blogPostManager->read($this->m_id);

			$template = new SimpleTemplate($blogPost['title'],
			                                $tr->_('BLOG_POST_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/blog-post_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main.template.php';
		}
	}
