<?php

	namespace Goji\Blog;

	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;
	use Exception;

	class BlogPostManager {

		/* <ATTRIBUTES> */

		private $m_parent;
		private $m_translator;
		private $m_form;

		/* <CONSTANTS> */

		const BLOG_POSTS_PATH = '../var/blog/';
		const BLOG_POSTS_EXTENSION = '.post.txt';

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
		 * @param \Goji\Blog\BlogPostControllerInterface $parent
		 * @param \Goji\Translation\Translator $tr
		 */
		public function __construct(BlogPostControllerInterface $parent, Translator $tr) {// TODO: inherit from BlogPostController() with blog post not exist method

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
		 * @return \Goji\Blog\BlogPostForm|null
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

		/**
		 * @param $id
		 */
		public function hydrateFormWithExistingBlogPost($id): void {

			// If bad ID, read() will fail
			$data = $this->read($id);

			$this->createForm(); // Creates one if none, doesn't overwrite current one

				$this->m_form->getInputByName('blog-post[title]')->setValue($data['title']);
				$this->m_form->getInputByName('blog-post[post]')->setValue($data['post']);
		}

	// <READ/WRITE>

		/*
		 * Overload these methods to change the saving mode (e.g. use a database)
		 */

		/**
		 * @param string $id
		 * @return bool
		 */
		protected function blogPostExists(string $id): bool {
			return is_file(self::BLOG_POSTS_PATH . $id . self::BLOG_POSTS_EXTENSION);
		}

		/**
		 * @param string $id
		 * @return array
		 */
		protected function getBlogPost(string $id): array {

			if (!$this->blogPostExists($id))
				$this->m_parent->errorBlogPostDoesNotExist();

			$file = fopen(self::BLOG_POSTS_PATH . $id . self::BLOG_POSTS_EXTENSION, 'r');

			if (!$file)
				$this->m_parent->errorBlogPostDoesNotExist();

			// Read headers
			while ($line = fgets($file)) {

				$line = rtrim($line); // Remove \n

				if ($line == '---')
					break; // Go to body

				if (mb_substr($line, 0, 7) == 'Title: ') {
					$data['title'] = mb_substr($line, 7);
				}

				if (mb_substr($line, 0, 6) == 'Date: ') {

					$date = mb_substr($line, 6);
					[$hour, $min, $sec, $month, $day, $year] = explode(',', $date);

					$data['date'] = [
						'hour' => (int) $hour,
						'min' => (int) $min,
						'sec' => (int) $sec,
						'month' => (int) $month,
						'day' => (int) $day,
						'year' => (int) $year
					];
				}
			}

			// Read body
			$data['post'] = stream_get_contents($file); // Read from caret to the end

			return $data;
		}

		/**
		 * @param string $id
		 * @return bool
		 */
		protected function deleteBlogPost(string $id): bool {
			return unlink(self::BLOG_POSTS_PATH . $id . self::BLOG_POSTS_EXTENSION);
		}

		/**
		 * Saves current form to disk, under given post ID
		 *
		 * @param string $id
		 * @return bool
		 * @throws \Exception
		 */
		protected function saveBlogPost(string $id): bool {

			if (!$this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			if ($id === null)
				return false;

			// Getting values
			$title = $this->m_form->getInputByName('blog-post[title]')->getValue();
				SwissKnife::removeNewLines($title);

			$date = date('H,i,s,n,j,Y'); // -> [$hour, $min, $sec, $month, $day, $year] = explode(',', $date);
			$post = $this->m_form->getInputByName('blog-post[post]')->getValue();

			$content = '';

				$content .= "Title: $title" . PHP_EOL;
				$content .= "Date: $date" . PHP_EOL;
				$content .= "---" . PHP_EOL;
				$content .= $post;

			if (!is_dir(self::BLOG_POSTS_PATH))
				mkdir(self::BLOG_POSTS_PATH, 0777, true);

			// file_put_contents() returns nb of bytes written (int) or false, hence the !== false
			return file_put_contents(self::BLOG_POSTS_PATH . $id . self::BLOG_POSTS_EXTENSION, $content) !== false;
		}

	// <CRUD>

		/*
		 * These are "save mode" independent, overload the READ/WRITE methods if you want
		 * to save the articles in a different way
		 */

		/**
		 * @param string $date
		 * @return string
		 */
		private function createIDFromDate(string $date): string {

			$i = 1;

			while (true) {

				$nb = str_pad((string) $i, 4, '0', STR_PAD_LEFT); // 1 -> 0001
				$id = $date . '.' . $nb;

				if (!$this->blogPostExists($id))
					return $id;

				$i++; // continue searching
			}

			return '';
		}

		/**
		 * Save to DB from form
		 *
		 * @return bool|string Returns ID if success, false on error
		 * @throws \Exception
		 */
		public function create() {

			if (!$this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			$id = $this->createIDFromDate(date('Y-m-d'));

			if ($this->saveBlogPost($id))
				return $id;
			else
				return false;
		}

		/**
		 * Read from DB
		 *
		 * @param $id
		 * @return array
		 */
		public function read($id): array {

			if ($id === null)
				$this->m_parent->errorBlogPostDoesNotExist();

			$id = (string) $id;

			if (!$this->blogPostExists($id))
				$this->m_parent->errorBlogPostDoesNotExist();

			// 404 if it doesn't work
			return $this->getBlogPost($id);
		}

		/**
		 * Update DB from form, overwrite the blog post with the given ID
		 *
		 * @param $id
		 * @return bool Returns true on success, false on error
		 * @throws \Exception
		 */
		public function update($id): bool {

			if (!$this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			if ($id === null)
				return false;

			$id = (string) $id;

			if (!$this->blogPostExists($id))
				$this->m_parent->errorBlogPostDoesNotExist();

			return $this->saveBlogPost($id);
		}

		/**
		 * Delete from DB
		 *
		 * @param $id
		 * @return bool
		 */
		public function delete($id): bool {

			if ($id === null)
				return false;

			$id = (string) $id;

			if (!$this->blogPostExists($id))
				$this->m_parent->errorBlogPostDoesNotExist();

			return $this->deleteBlogPost($id);
		}
	}
