<?php

	namespace Goji\Blog;

	use Goji\Blueprints\ControllerInterface;

	interface BlogPostControllerInterface extends ControllerInterface {

		public function errorBlogPostDoesNotExist();
	}
