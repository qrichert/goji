<?php

	namespace Goji\Blog;

	use Goji\Core\App;

	/**
	 * Class BlogAdminControllerAbstract
	 *
	 * @package Goji\Blog
	 */
	abstract class BlogAdminControllerAbstract extends BlogControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			// On admin, you never 'read', you either create, update or delete
			if ($this->m_action != BlogPostManager::ACTION_CREATE
			    && $this->m_action != BlogPostManager::ACTION_UPDATE
			    && $this->m_action != BlogPostManager::ACTION_DELETE)
					$this->m_action = BlogPostManager::ACTION_CREATE; // Default
		}
	}
