<?php

	namespace Goji\Blog;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\App;

	/**
	 * Class BlogPostControllerAbstract
	 *
	 * @package Goji\Blog
	 */
	abstract class BlogPostControllerAbstract extends ControllerAbstract {

		public function getApp(): App {
			return $this->m_app;
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}
	}
