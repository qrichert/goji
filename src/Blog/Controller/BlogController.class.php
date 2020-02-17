<?php

namespace Blog\Controller;

use Blog\Blueprint\BlogTrait;
use Blog\Model\BlogPostManager;
use Blog\Model\BlogSearchForm;
use Goji\Core\App;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class BlogController extends BlogControllerAbstract {

	use BlogTrait;

	/* <CONSTANTS> */

	public function __construct(App $app) {

		parent::__construct($app);

		$this->activateCacheIfRolePermits();
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$blogSearchForm = new BlogSearchForm($tr);

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
