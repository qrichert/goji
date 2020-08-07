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

			// TODO: put that sanitation in BlogManager
			foreach ($categories as &$category) {
				// Make ID integer or null if none (new category)
				$category['id'] = !empty($category['id']) ? (int) $category['id'] : null;
				// Clean category name
				$category['name'] = (string) $category['name'];
				$category['name'] = trim($category['name']);
				$category['name'] = preg_replace(\Goji\Parsing\RegexPatterns::whiteSpace(), ' ', $category['name']);
			}
			unset($category);
		}

		\Goji\Debug\Logger::dump($categories);

		// Give new category to blog manager, it will save them,
		// then fetch them using SQL to sort & shit so it's same order as what will be show everywhere else
		// + most important WITH IDs
		// DELETE FROM categories WHERE locale = currentLocale AND id NOT IN(all ids)
		// foreach:
		//     if ID: UPDATE categories, SET name = newname WHERE id=ID
		//     if NO ID: INSERT INTO categories NEW CATEGORY
		// Then send them back to update order in interface

		// $blogManager = new BlogManager($this->m_app);
		// 	$blogManager->setCategories($categories);

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
