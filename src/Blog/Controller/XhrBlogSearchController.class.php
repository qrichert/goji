<?php

namespace Blog\Controller;

use Blog\Blueprint\BlogTrait;
use Blog\Model\BlogPostManager;
use Blog\Model\BlogSearchForm;
use Goji\Core\App;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Translation\Translator;

class XhrBlogSearchController extends BlogControllerAbstract {

	use BlogTrait;

	public function __construct(App $app) {

		parent::__construct($app);

		// Emulate XhrControllerAbstract
		HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX);

		if (!$this->m_app->getRequestHandler()->isAjaxRequest())
			$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('home'));
	}

	private function treatForm(Form $form) {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
			], false);
		}

		$formQuery = $form->getInputByName('blog-search[query]')->getValue();

		$blogPostManager = new BlogPostManager($this);
		$blogPosts = $blogPostManager->getBlogPostsForQuery(
													$formQuery,
													0,
		                                            -1,
		                                            $this->m_app->getLanguages()->getCurrentCountryCode(),
		                                            [self::class, 'renderCleanAndCut']);

		HttpResponse::JSON([
			'message' => $formQuery,
			'posts' => $blogPosts
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
		$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new BlogSearchForm($tr);
		$form->hydrate();

		$this->treatForm($form);
	}
}
