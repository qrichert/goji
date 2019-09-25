<?php

	namespace App\Controller;

	use Goji\Blog\BlogControllerAbstract;
	use Goji\Blog\BlogPostManager;
	use Goji\Core\App;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class BlogPostController extends BlogControllerAbstract {

		/* <ATTRIBUTES> */

		private $m_id;

		public function __construct(App $app) {

			parent::__construct($app);

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
			$blogPost = $blogPostManager->read($this->m_id, true);

			$template = new SimpleTemplate($blogPost['title'] . ' - ' . $this->m_app->getSiteName(),
			                                $tr->_('BLOG_POST_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('BlogPostView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}