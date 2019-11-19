<?php

	namespace App\Controller\Admin;

	use Goji\Blog\BlogAdminControllerAbstract;
	use Goji\Blog\BlogPostManager;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrAdminBlogPostController extends BlogAdminControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			// Emulate XhrControllerAbstract
			HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX);

			if (!$this->m_app->getRequestHandler()->isAjaxRequest())
				$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('home'));
		}

		private function treatForm(BlogPostManager $manager): void {

			$tr = $this->m_app->getTranslator();

			$detail = [];

			if (!$manager->getForm()->isValid($detail)) {

				HttpResponse::JSON([
					'message' => $tr->_('BLOG_POST_ERROR'),
					'detail' => $detail
				], false);
			}

			$actionSuccess = false;

			if ($this->m_action == BlogPostManager::ACTION_UPDATE)
				$actionSuccess = $manager->update($this->m_blogPostID); // bool
			else
				$actionSuccess = $manager->create(); // string|false

			if ($actionSuccess !== false) {

				// If CREATE and SUCCESS, $actionSuccess contains new ID
				if ($this->m_action == BlogPostManager::ACTION_CREATE)
					$this->m_blogPostID = $actionSuccess;

				$redirectTo = false;

				if ($this->m_action == BlogPostManager::ACTION_CREATE)
					$redirectTo = "blog-post?action=" . BlogPostManager::ACTION_UPDATE . "&id={$this->m_blogPostID}";

				$message = $this->m_action == BlogPostManager::ACTION_UPDATE ?
					$tr->_('BLOG_POST_UPDATE_SUCCESS') :
					$tr->_('BLOG_POST_SUCCESS');

				HttpResponse::JSON([
					'message' => $message,
					'id' => $this->m_blogPostID,
					'permalink' => $manager->getForm()->getInputByName('blog-post[permalink]')->getValue(),
					'redirect' => $redirectTo
				], true);
			}

			HttpResponse::JSON([
				'message' => $tr->_('BLOG_POST_WRITING_FAILED')
			], false);
		}

		public function render(): void {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this);
				$blogPostManager->createForm();
				$blogPostManager->hydrateFormWithPostData();

			$this->treatForm($blogPostManager);
		}
	}
