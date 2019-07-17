<?php

	namespace App\Controller\Admin;

	use App\Model\Blog\BlogPostManager;
	use Goji\Blueprints\ControllerInterface;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleTemplate;

	class AdminBlogPostController implements ControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_action;
		private $m_blogPostID;

		public function __construct(App $app) {

			$this->m_app = $app;

			$this->m_action = $_GET['action'] ?? BlogPostManager::ACTION_CREATE;
				$this->m_action = mb_strtolower($this->m_action);

				if ($this->m_action != BlogPostManager::ACTION_CREATE
					&& $this->m_action != BlogPostManager::ACTION_UPDATE)
						$this->m_action = BlogPostManager::ACTION_CREATE; // Default

			$this->m_blogPostID = $_GET['id'] ?? null;
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}

		private function treatForm(Translator $tr, BlogPostManager $manager): bool {

			$detail = [];
			$isValid = $manager->getForm()->isValid($detail);

			if ($isValid) {

				// TODO: Save or Update ($manager->save() { if create: self->create() else if update: self->update()) }

				// If AJAX, return JSON (SUCCESS)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'message' => $tr->_('BLOG_POST_SUCCESS'),
						'clear' => $this->m_action == BlogPostManager::ACTION_CREATE // Clean the form if we are in create mode
					], true);
				}

				// Clean the form if we are in create mode
				if ($this->m_action == BlogPostManager::ACTION_CREATE)
					$manager->clearForm();

				return true;
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

			$blogPostManager = new BlogPostManager($this, $tr);
				$blogPostManager->createForm();
$blogPostManager->getForm()->prepareForUpdate('id-hello56789');
			$formSentSuccess = null;

			// If data sent
			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$blogPostManager->hydrateFormWithPostData();
				$formSentSuccess = $this->treatForm($tr, $blogPostManager);

			} else {

				// If we update, we fetch the current values
				if ($this->m_action == BlogPostManager::ACTION_UPDATE)
					$blogPostManager->hydrateFormWithExistingBlogPost($this->m_blogPostID);
			}

			// Template
			$template = new SimpleTemplate($tr->_('BLOG_POST_PAGE_TITLE'),
										   $tr->_('BLOG_POST_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/Admin/admin_blog_post_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main.template.php';
		}
	}
