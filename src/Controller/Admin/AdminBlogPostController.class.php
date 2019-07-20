<?php

	namespace App\Controller\Admin;

	use Goji\Blog\BlogPostControllerAbstract;
	use Goji\Blog\BlogPostManager;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleTemplate;

	class AdminBlogPostController extends BlogPostControllerAbstract {

		/* <ATTRIBUTES> */

		private $m_action;
		private $m_blogPostID;

		public function __construct(App $app) {

			parent::__construct($app);

			$this->m_action = $_GET['action'] ?? BlogPostManager::ACTION_CREATE;
				$this->m_action = mb_strtolower($this->m_action);

				if ($this->m_action != BlogPostManager::ACTION_CREATE
					&& $this->m_action != BlogPostManager::ACTION_UPDATE
					&& $this->m_action != BlogPostManager::ACTION_DELETE)
						$this->m_action = BlogPostManager::ACTION_CREATE; // Default

			$this->m_blogPostID = $_GET['id'] ?? null;
		}

		private function treatForm(Translator $tr, BlogPostManager $manager): bool {

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

					// If AJAX, return JSON (SUCCESS)
					if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

						$message = $this->m_action == BlogPostManager::ACTION_UPDATE ?
							$tr->_('BLOG_POST_UPDATE_SUCCESS') :
							$tr->_('BLOG_POST_SUCCESS');

						HttpResponse::JSON([
							'message' => $message,
							'id' => $this->m_blogPostID,
							'redirect' => $redirectTo
						], true);
					}

					// Redirect to edit page
					if ($this->m_action == BlogPostManager::ACTION_CREATE) {

						// Just in case
						$manager->clearForm();
						$this->m_app->getRouter()->redirectTo($redirectTo);
					}

					return true;
				}

				// If AJAX, return JSON (ERROR)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'message' => $tr->_('BLOG_POST_WRITING_FAILED')
					], false);
				}

				return false;
			}

			// If AJAX, return JSON (ERROR)
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON([
					'message' => $tr->_('BLOG_POST_ERROR'),
					'detail' => $detail
				], false);
			}

			return false;
		}

		public function render() {

			// Translation
			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$blogPostManager = new BlogPostManager($this, $tr, $this->m_action);
				$blogPostManager->createForm();

			$formSentSuccess = null;

			// If data sent
			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$blogPostManager->hydrateFormWithPostData();
				$formSentSuccess = $this->treatForm($tr, $blogPostManager);

			} else {

				// If we update, we fetch the current values
				if ($this->m_action == BlogPostManager::ACTION_UPDATE) {

					$blogPostManager->getForm()->getInputByName('blog-post[permalink]')->setAttribute('readonly');
					$blogPostManager->hydrateFormWithExistingBlogPost($this->m_blogPostID);

				} elseif ($this->m_action == BlogPostManager::ACTION_DELETE) {

					$blogPostManager->delete($this->m_blogPostID);

					$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('blog'));
				}
			}

			// Template
			$template = new SimpleTemplate($tr->_('BLOG_POST_PAGE_TITLE'),
										   $tr->_('BLOG_POST_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/View/Admin/AdminBlogPostView.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main.template.php';
		}
	}
