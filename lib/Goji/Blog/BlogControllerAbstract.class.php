<?php

	namespace Goji\Blog;

	use Goji\Blueprints\ControllerAbstract;

	/**
	 * Class BlogControllerAbstract
	 *
	 * @package Goji\Blog
	 */
	abstract class BlogControllerAbstract extends ControllerAbstract {

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}
	}
