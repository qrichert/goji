<?php

namespace Blog\Controller\Admin;

use Blog\Model\BlogCategories;
use Blog\Model\BlogManager;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Toolkit\SwissKnife;
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

		$categories = [];

		if (!empty($_POST['blog-categories__categories']['id'])
			&& !empty($_POST['blog-categories__categories']['name'])) {

			$categories = SwissKnife::zip(
				$_POST['blog-categories__categories']['id'],
				$_POST['blog-categories__categories']['name'],
				['id', 'name']
			);
		}

		$blogManager = new BlogManager($this->m_app);
			$blogManager->setCategories($categories);

		HttpResponse::JSON([
			'categories' => $blogManager->getCategories()
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
