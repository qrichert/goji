<?php

	namespace App\Model\Blog;

	use App\Controller\Admin\AdminBlogPostController;
	use Goji\Translation\Translator;
	use Exception;

	class BlogPostManager {

		/* <ATTRIBUTES> */

		private $m_parent;
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

		const E_NO_FORM = 0;

		/**
		 * BlogPostManager constructor.
		 *
		 * @param \App\Controller\Admin\AdminBlogPostController $parent
		 * @param \Goji\Translation\Translator $tr
		 */
		public function __construct(AdminBlogPostController $parent, Translator $tr) {

			$this->m_parent = $parent;
			$this->m_translator = $tr;

			$this->m_form = null;
		}

		/**
		 * @return bool true if form is set, else false
		 */
		public function hasForm(): bool {
			return isset($this->m_form) && $this->m_form instanceof BlogPostForm;
		}

		/**
		 * @return \App\Model\Blog\BlogPostForm|null
		 */
		public function getForm(): ?BlogPostForm {
			return $this->m_form;
		}

		/**
		 * Create a BlogPostForm
		 *
		 * @return bool true if created, false if already exists
		 */
		public function createForm(): bool {

			if ($this->hasForm())
				return false;

			$this->m_form = new BlogPostForm($this->m_translator);

			return true;
		}

		/**
		 * Clears the form
		 */
		public function clearForm(): void {
			$this->m_form = null;
			$this->createForm();
		}

		/**
		 * Create a form if none, and hydrate it with POST data
		 */
		public function hydrateFormWithPostData(): void {

			$this->createForm(); // Creates one if none, doesn't overwrite current one

			$this->m_form->hydrate();
		}

		public function hydrateFormWithExistingBlogPost($id): void {

			if ($id == null)
				$this->m_parent->errorBlogPostDoesNotExist();
			// TODO: if ID doesn't exist also

			$this->createForm(); // Creates one if none, doesn't overwrite current one

			// TODO: hydrateFormWithExistingBlogPost()
		}

		/**
		 * Save to DB from form
		 *
		 * @return bool
		 * @throws \Exception
		 */
		public function create(): bool {

			if ($this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			return true;
		}

		/**
		 * Read from DB
		 *
		 * @return array
		 */
		public function read(): array {
			return [];
		}

		/**
		 * Update DB from form, overwrite the blog post with the given ID
		 *
		 * @return bool
		 * @throws \Exception
		 */
		public function update($id): bool {

			if ($this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			// TODO: if (!exists($id))
			//$this->m_parent->errorBlogPostDoesNotExist();

			return true;
		}

		/**
		 * Delete from DB
		 *
		 * @return bool
		 */
		public function delete($id): bool {

			// TODO: if (!exists($id))
			//$this->m_parent->errorBlogPostDoesNotExist();

			return true;
		}
	}
