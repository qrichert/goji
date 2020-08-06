<?php

namespace Blog\Controller\Admin;

use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class AdminBlogCategories extends ControllerAbstract {
	public function render(): void {
		// Translation
		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		// Template
		$template = new SimpleTemplate($tr->_('BLOG_CATEGORIES_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
									   $tr->_('BLOG_CATEGORIES_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		// Getting the view (into buffer)
		require_once $template->getView('Blog/Admin/BlogCategoriesView');

		// Now the view is accessible as string w/ $template->getPageContent()
		$template->saveBuffer();

		// Inside the template file we call $template to put things in place.
		require_once $template->getTemplate('page/main');
	}
}
