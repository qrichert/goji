<?php

	namespace App\Controller;

	use Goji\Blog\BlogControllerAbstract;
	use Goji\Blog\BlogPostManager;
	use Goji\Core\App;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class BlogController extends BlogControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			$this->activateCacheIfRolePermits();
		}

		public function render(): void {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this);
			$blogPosts = $blogPostManager->getBlogPosts(0, -1, $this->m_app->getLanguages()->getCurrentCountryCode(), 250, true);

			$template = new SimpleTemplate($tr->_('BLOG_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
			                                $tr->_('BLOG_PAGE_DESCRIPTION'));

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('BlogView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
