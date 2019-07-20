<?php

	namespace Goji\Blog;

	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;
	use Exception;

	/**
	 * Class BlogPostManager
	 *
	 * /!\ This class is NOT fit for production /!\
	 *
	 * It just saves the blog posts in files. It is very very inefficient.
	 * This is just the "base", so I can provide an example.
	 *
	 * To use this in production, you should INHERIT from this class and OVERLOAD
	 * ALL read/write function, to make it work with a database or something.
	 * (Basically all non-private functions in the // <READ/WRITE> part
	 *
	 * @package Goji\Blog
	 */
	class BlogPostManager {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_parent;
		private $m_translator;
		private $m_form;
		private $m_verifyPermalink;

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
		 * @param \Goji\Blog\BlogPostControllerAbstract $parent
		 * @param \Goji\Translation\Translator $tr
		 * @param string|null $defaultAction The main reason BlogPostManager has been called
		 */
		public function __construct(BlogPostControllerAbstract $parent, Translator $tr, string $defaultAction = null) {

			$this->m_app = $parent->getApp();
			$this->m_parent = $parent;
			$this->m_translator = $tr;

			$this->m_form = null;

			$this->m_verifyPermalink = $defaultAction === self::ACTION_CREATE;
		}

		/**
		 * @return bool
		 */
		public function getVerifyPermalink(): bool {
			return $this->m_verifyPermalink;
		}

		/**
		 * @param bool $verify
		 */
		public function setVerifyPermalink(bool $verify): void {
			$this->m_verifyPermalink = $verify;
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

				$this->m_form->getInputByName('blog-post[permalink]')->setValue($data['permalink']);
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
		 * Array will be way bigger than what we need
		 *
		 * array_reverse() copies the array, here at least we don't copy it
		 *
		 * @param int $offset
		 * @return \Generator
		 */
		private function getBlogPostsPostByPostReverse(int $offset) {

			$posts = glob(self::BLOG_POSTS_PATH . '*' . self::BLOG_POSTS_EXTENSION);

			$count = count($posts) - 1; // - 1 because array starts at 0
			$count -= $offset;

			for ($i = $count; $i >= 0; $i--)
				yield $posts[$i];
		}

		/**
		 * Returns a list of blog post entries
		 *
		 * @param int $offset
		 * @param int $count -1 = all, infinite
		 * @param string $locale
		 * @param int $cutContentAtNbChars
		 * @param bool $stripHTMLTags
		 * @return array
		 */
		public function getBlogPosts(int $offset = 0, int $count = -1, string $locale = null, int $cutContentAtNbChars = -1, bool $stripHTMLTags = false): array {

			if ($offset < 0)
				$offset = 0;

			$posts = [];
			$i = 0;

			foreach ($this->getBlogPostsPostByPostReverse($offset) as $post) {

				$post = $this->readBlogPostFromFile($post, $cutContentAtNbChars, $stripHTMLTags);

				if (!empty($locale) && !empty($post['locale'])) { // We care about the locale

					// if blog post locale doesn't start with requested locale, skip the blog post
					// en_US, request en -> YES | en, request en_US -> NO | fr, request en -> NO
					if (mb_substr($post['locale'], 0, mb_strlen($locale)) !== $locale)
						continue;
				}

				$posts[] = $post;

				$i++;
				if ($i == $count)
					break;
			}

			return $posts;
		}

		/**
		 * @param string $id
		 * @return array
		 */
		protected function getBlogPost(string $id): array {

			if (!$this->blogPostExists($id))
				$this->m_parent->errorBlogPostDoesNotExist();

			return $this->readBlogPostFromFile(self::BLOG_POSTS_PATH . $id . self::BLOG_POSTS_EXTENSION);
		}

		/**
		 * @param string $permalink
		 * @return string|null
		 */
		private function getIDForPermalink(string $permalink): ?string {

			$id = null;

			$posts = glob(self::BLOG_POSTS_PATH . '*' . self::BLOG_POSTS_EXTENSION);

			foreach ($posts as $file) {

				$file = fopen($file, 'r');

				if (!$file)
					continue;

				$pl = rtrim(fgets($file)); // Read first link && rtrim() to remove \n
					$pl = mb_substr($pl, 11); // Remove 'Permalink: '

				if ($permalink == $pl) { // Found a match
					$id = rtrim(fgets($file)); // Read second line (ID)
						$id = mb_substr($id, 4); // 'ID: '
				}

				fclose($file);

				if (!empty($id))
					return $id;
			}

			return null;
		}

		/**
		 * @param string $file
		 * @param int $cutContentAtNbChars -1 = infinite, whole text
		 * @param bool $stripHTMLTags
		 * @return array
		 */
		private function readBlogPostFromFile(string $file, int $cutContentAtNbChars = -1, bool $stripHTMLTags = false): array {

			$file = fopen($file, 'r');

			if (!$file)
				$this->m_parent->errorBlogPostDoesNotExist();

			// Read headers
			while ($line = fgets($file)) {

				$line = rtrim($line); // Remove \n

				if ($line == '---')
					break; // Go to body

				if (mb_substr($line, 0, 11) == 'Permalink: ') {
					$data['permalink'] = mb_substr($line, 11);
				}

				if (mb_substr($line, 0, 4) == 'ID: ') {
					$data['id'] = mb_substr($line, 4);
				}

				if (mb_substr($line, 0, 8) == 'Locale: ') {
					$data['locale'] = mb_substr($line, 8);
				}

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

			if ($stripHTMLTags)
				$data['post'] = strip_tags($data['post']);

			if ($cutContentAtNbChars > 0 && mb_strlen($data['post']) > $cutContentAtNbChars)
				$data['post'] = SwissKnife::ceil_str($data['post'], $cutContentAtNbChars) . '...';

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
		 * If permalink already exists, add *-2, if *-2 exists, add *-3, etc.
		 *
		 * @param string $permalink
		 * @return string
		 */
		protected function makePermalinkUnique(string $permalink): string {

			$id = $this->getIDForPermalink($permalink);
			$base = $permalink;
			$i = 2;

			while ($id !== null) { // while (permalink found)
				$permalink = $base . '-' . $i;
				$id = $this->getIDForPermalink($permalink);
				$i++;
			}

			// Unique permalink after loop !

			return $permalink;
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

			// Permalink last
			$permalink = $this->m_form->getInputByName('blog-post[permalink]')->getValue();

			if ($this->m_verifyPermalink) { // On update, just keep the readonly value

				if (empty($permalink))
					$permalink = $title;

				$permalink = SwissKnife::stringToID($permalink);
				$permalink = $this->makePermalinkUnique($permalink);
			}

			$content = '';

				$content .= "Permalink: $permalink" . PHP_EOL; // Must be first
				$content .= "ID: $id" . PHP_EOL; // Must be second
				$content .= "Locale: {$this->m_translator->getTargetLocale()}" . PHP_EOL;
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

			$date .= '.' . time();

			$i = 1;

			while (true) {

				$id = $date . '.' . $i;

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

			if (!$this->blogPostExists($id)) {

				// Maybe it's a permalink, so get the ID from it
				$id = $this->getIDForPermalink($id);

				if (empty($id) || !$this->blogPostExists($id))
					$this->m_parent->errorBlogPostDoesNotExist();
			}

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
