<?php

namespace Blog\Controller\Admin;

use Blog\Model\BlogCategories;
use Blog\Model\BlogManager;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Toolkit\TagManager;
use Goji\Translation\Translator;

class XhrAdminBlogCategoriesController extends XhrControllerAbstract {

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'message' => $tr->_('BLOG_CATEGORIES_FORM_ERROR'),
				'detail' => $detail
			], false);
		}

		$categories = $form->getInputByName('blog-categories[categories]')->getValue();

		// Cleaning categories to sorted array of unique values
		$categories = preg_split('#\R#', $categories); // Split by lines
		$categories = TagManager::sanitizeTags($categories);

		$blogManager = new BlogManager($this->m_app);
			$blogManager->setCategories($categories);

		HttpResponse::JSON([
			'categories' => $categories
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new BlogCategories($tr, $this->m_app);
			$form->hydrate();

		$this->treatForm($form);
	}
}
