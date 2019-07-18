<?php

	namespace Goji\Blog;

	use Goji\Blueprints\ControllerInterface;
	use Goji\Core\App;

	abstract class BlogPostControllerAbstract implements ControllerInterface {

		/* <ATTRIBUTES> */

		protected $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		public function getApp(): App {
			return $this->m_app;
		}

		public function errorBlogPostDoesNotExist(): void {
			$this->m_app->getRouter()->requestErrorDocument(self::HTTP_ERROR_NOT_FOUND);
		}
	}
