<?php

namespace Blog\Controller\Admin;

use Blog\Model\BlogCategories;
use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class AdminBlogCategoriesController extends ControllerAbstract {

	public function render(): void {

		// Translation
		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		// Form
		// TODO: /!\ Hydrate according to locale !!!
		$blogCategoriesForm = new BlogCategories($tr, $this->m_app);
			$blogCategoriesForm->getInputById('blog-categories__categories')->setValue(json_encode([
				['id' => 1, 'name' => 'Food'],
				['id' => 2, 'name' => 'Health'],
			]));

		// Template
		$template = new SimpleTemplate($tr->_('BLOG_CATEGORIES_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
									   $tr->_('BLOG_CATEGORIES_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		// Getting the view (into buffer)
		require_once $template->getView('Blog/Admin/AdminBlogCategoriesView');

		// Now the view is accessible as string w/ $template->getPageContent()
		$template->saveBuffer();

		// Inside the template file we call $template to put things in place.
		require_once $template->getTemplate('page/main');
	}
}
