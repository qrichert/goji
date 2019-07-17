<?php

	namespace App\Model\Blog;

	use App\Controller\Admin\AdminBlogPostController;
	use Goji\Translation\Translator;
	use Exception;

	class BlogPostManager {

		/* <ATTRIBUTES> */

		private $m_parent;
		private $m_action;
		private $m_id;
		private $m_translator;
		private $m_form;

		/* <CONSTANTS> */

		const ACTION_CREATE = 'create';
		const ACTION_READ = 'read';
		const ACTION_UPDATE = 'update';
		const ACTION_DELETE = 'delete';

		const ALLOWED_ACTIONS = [
			self::ACTION_CREATE,
			self::ACTION_READ,
			self::ACTION_UPDATE,
			self::ACTION_DELETE
		];

		public function __construct(AdminBlogPostController $parent, string $action, $id, Translator $tr) {

			$this->m_parent = $parent;

			if (!in_array($action, self::ALLOWED_ACTIONS))
				$this->m_parent->errorActionUnknown();

			$this->m_action = $action;
			$this->m_id = $id;
			$this->m_translator = $tr;

			if ($this->m_id === null && $this->m_action != self::ACTION_CREATE)
				$this->m_parent->errorBlogPostDoesNotExist();

			$this->m_form = new BlogPostForm($this->m_translator);
		}

		public function getForm(): BlogPostForm {

			return $this->m_form;
		}

		public function clearForm(): void {

			$this->m_form = new BlogPostForm($this->m_translator);
		}
	}
