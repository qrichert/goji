<?php

	namespace App\Controller;

	use Goji\Blog\BlogPostControllerInterface;
	use Goji\Blog\BlogPostManager;
	use Goji\Core\App;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	/*
	 * TODO: get specific locale or country code
	 */
	class BlogController implements BlogPostControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this, $tr);
			$blogPosts = $blogPostManager->getBlogPosts(0, -1);

			$template = new SimpleTemplate($tr->_('BLOG_PAGE_TITLE'),
			                                $tr->_('BLOG_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/blog_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main.template.php';
		}
	}
