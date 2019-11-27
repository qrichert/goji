<?php

	namespace Goji\Blog;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\App;

	/**
	 * Class BlogControllerAbstract
	 *
	 * @package Goji\Blog
	 */
	abstract class BlogControllerAbstract extends ControllerAbstract {

		/* <ATTRIBUTES> */

		protected $m_action;
		protected $m_blogPostID;

		public function __construct(App $app) {

			parent::__construct($app);

			$this->m_action = $_GET['action'] ?? BlogPostManager::ACTION_READ;
				$this->m_action = mb_strtolower($this->m_action);

				if ($this->m_action != BlogPostManager::ACTION_CREATE
					&& $this->m_action != BlogPostManager::ACTION_UPDATE
					&& $this->m_action != BlogPostManager::ACTION_DELETE)
						$this->m_action = BlogPostManager::ACTION_READ; // Default

			$this->m_blogPostID = $_GET['id'] ?? null;
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}
	}
