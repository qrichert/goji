<?php

	namespace App\Controller\Admin;

	use Goji\Blog\BlogPostManager;
	use Goji\Blog\BlogControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrAdminBlogPostController extends BlogControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			// Emulate XhrControllerAbstract
			HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX);

			if (!$this->m_app->getRequestHandler()->isAjaxRequest())
				$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('home'));
		}

		private function treatForm(Translator $tr, BlogPostManager $manager): void {

			$detail = [];
			$isValid = $manager->getForm()->isValid($detail);

			if ($isValid) {

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

			HttpResponse::JSON([
				'message' => $tr->_('BLOG_POST_ERROR'),
				'detail' => $detail
			], false);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this, $tr);
				$blogPostManager->createForm();
				$blogPostManager->hydrateFormWithPostData();

			$this->treatForm($tr, $blogPostManager);
		}
	}
