<?php

namespace Blog\Controller\Admin;

use Blog\Model\BlogPostManager;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class AdminBlogPostController extends BlogAdminControllerAbstract {

	public function render(): void {

		// Translation
		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$formAction = 'xhr-blog-post';

		$formActions = [];

			if (!empty($this->m_action))
				$formActions[] = 'action=' . $this->m_action;

			if (!empty($this->m_blogPostID))
				$formActions[] = 'id=' . $this->m_blogPostID;

			if (!empty($formActions))
				$formAction .= '?' . implode('&', $formActions);

		$blogPostManager = new BlogPostManager($this);
			$blogPostManager->createForm();
			$blogPostManager->getForm()->setAction($formAction);

		if ($this->m_action == BlogPostManager::ACTION_CREATE) {

			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][year]')->setValue(date('Y'));
			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][month]')->setValue(date('m'));
			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][day]')->setValue(date('d'));
			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][hours]')->setValue(date('H'));
			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][minutes]')->setValue(date('i'));
			$blogPostManager->getForm()->getInputByName('blog-post[publication-date][seconds]')->setValue(date('s'));

		// If we update, we fetch the current values
		} else if ($this->m_action == BlogPostManager::ACTION_UPDATE) {

			$blogPostManager->hydrateFormWithExistingBlogPost($this->m_blogPostID);

		} else if ($this->m_action == BlogPostManager::ACTION_DELETE) {

			$blogPostManager->delete($this->m_blogPostID);

			$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('blog'));
		}

		// Template
		$template = new SimpleTemplate($tr->_('BLOG_POST_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
									   $tr->_('BLOG_POST_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		// Getting the view (into buffer)
		require_once $template->getView('Blog/Admin/AdminBlogPostView');

		// Now the view is accessible as string w/ $template->getPageContent()
		$template->saveBuffer();

		// Inside the template file we call $template to put things in place.
		require_once $template->getTemplate('page/main');
	}
}
