<?php

	namespace Goji\Blog;

	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;
	use Exception;

	/**
	 * Class BlogPostManager
	 *
	 * @package Goji\Blog
	 */
	class BlogPostManager {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_db;
		private $m_parent;
		private $m_translator;
		private $m_form;

		/* <CONSTANTS> */

		const BLOG_POSTS_PATH = ROOT_PATH . '/var/blog/';
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
		 * @param \Goji\Blog\BlogControllerAbstract $parent
		 * @throws \Exception
		 */
		public function __construct(BlogControllerAbstract $parent) {

			$this->m_app = $parent->getApp();
			$this->m_db = $this->m_app->getDatabase();
			$this->m_parent = $parent;
			$this->m_translator = $this->m_app->getTranslator();

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

				$this->m_form->getInputByName('blog-post[permalink]')->setValue($data['permalink']);
				$this->m_form->getInputByName('blog-post[title]')->setValue($data['title']);
				$this->m_form->getInputByName('blog-post[post]')->setValue($data['post']);
		}

	// <READ/WRITE>

		/**
		 * Returns a list of blog post entries
		 *
		 * @param int $offset
		 * @param int $count -1 = all, infinite
		 * @param string $locale
		 * @param callable|null $formatFunction
		 * @return array
		 */
		public function getBlogPosts(int $offset = 0, int $count = -1, string $locale = null, callable $formatFunction = null): array {

			if ($offset < 0)
				$offset = 0;

			$q = 'SELECT * FROM g_blog ';
			$params = [];

			if (!empty($locale)) {
				$q .= 'WHERE locale LIKE :locale ';
				$params['locale'] = "$locale%";
			}

			$q .= 'ORDER BY id DESC ';

			if ($offset > 0 || $count > -1)
				$q .= 'LIMIT ' . ((string) $offset);

			if ($count > -1)
				$q .= ', ' . ((string) $count);

			$query = $this->m_db->prepare($q);
			$query->execute($params);

			$reply = $query->fetchAll();

			$query->closeCursor();

			if ($formatFunction !== null) {

				foreach ($reply as &$entry) {
					$entry['post'] = $formatFunction($entry['post']);
				}
				unset($entry);
			}

			foreach ($reply as &$entry) {
				$entry['creation_date'] = SwissKnife::dateToComponents($entry['creation_date']);
				$entry['last_edit_date'] = SwissKnife::dateToComponents($entry['last_edit_date']);
			}
			unset($entry);

			return $reply;
		}

		/**
		 * If permalink already exists, add *-2, if *-2 exists, add *-3, etc.
		 *
		 * @param string $permalink
		 * @return string
		 */
		protected function makePermalinkUnique(string $permalink): string {

			$permalinkExists = true;
			$modifiedPermalink = '';
			$i = 1;

			do {
				// First time keep it as is, next and up add -1, -2, -3, etc.
				$modifiedPermalink = $permalink . ($i > 1 ? ('-' . $i) : '');

				$query = $this->m_db->prepare('SELECT COUNT(*) AS nb
														 FROM g_blog
														 WHERE permalink=:permalink');

				$query->execute([
					'permalink' => $modifiedPermalink
				]);

				$reply = $query->fetch();
					$reply = (int) $reply['nb'];

				$query->closeCursor();

				if ($reply === 0)
					$permalinkExists = false;
				else
					$i++;

			} while ($permalinkExists);

			return $modifiedPermalink;
		}

	// <CRUD>

		/**
		 * Save to DB from form
		 *
		 * @return int Returns ID of new blog post
		 * @throws \Exception
		 */
		public function create(): int {

			if (!$this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			// Getting values

			// Title
			$title = $this->m_form->getInputByName('blog-post[title]')->getValue();
				SwissKnife::removeNewLines($title);

			// Post
			$post = $this->m_form->getInputByName('blog-post[post]')->getValue();

			// Permalink
			$permalink = $this->m_form->getInputByName('blog-post[permalink]')->getValue();

				if (empty($permalink))
					$permalink = $title;

				$permalink = SwissKnife::stringToID($permalink);
				$permalink = $this->makePermalinkUnique($permalink);

				// Update form with new permalink
				$this->m_form->getInputByName('blog-post[permalink]')->setValue($permalink);

			// Locale
			$locale = $this->m_translator->getTargetLocale();

			// Save
			$query = $this->m_db->prepare('INSERT INTO g_blog
											       ( locale,  permalink,  creation_date,  last_edit_date,  title,  post,  created_by)
											VALUES (:locale, :permalink, :creation_date, :last_edit_date, :title, :post, :created_by)');

			$query->execute([
				'locale' => $locale,
				'permalink' => $permalink,
				'creation_date' => date('Y-m-d H:i:s'),
				'last_edit_date' => date('Y-m-d H:i:s'),
				'title' => $title,
				'post' => $post,
				'created_by' => $this->m_app->getUser()->getId()
			]);

			$query->closeCursor();

			return (int) $this->m_db->lastInsertId();
		}

		/**
		 * Read from DB
		 *
		 * @param $id
		 * @param bool $isPermalink If we read from a permalink
		 * @return array
		 */
		public function read($id, $isPermalink = false): array {

			if ($id === null)
				$this->m_parent->errorBlogPostDoesNotExist();

			$q = 'SELECT * FROM g_blog WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

			$query = $this->m_db->prepare($q);
			$query->execute([
				'id' => $id
			]);

			$reply = $query->fetch();

			$query->closeCursor();

			if ($reply === false)
				$this->m_parent->errorBlogPostDoesNotExist();

			$reply['creation_date'] = SwissKnife::dateToComponents($reply['creation_date']);
			$reply['last_edit_date'] = SwissKnife::dateToComponents($reply['last_edit_date']);

			return $reply;
		}

		/**
		 * Update DB from form, overwrite the blog post with the given ID
		 *
		 * @param $id
		 * @param bool $isPermalink If we read from a permalink
		 * @return bool Returns true on success, false on error
		 * @throws \Exception
		 */
		public function update($id, $isPermalink = false): bool {

			if (!$this->hasForm())
				throw new Exception("No form is set. You must create a form first, use BlogPostManager::createForm().", self::E_NO_FORM);

			if ($id === null)
				return false;

			// Getting values

			// Title
			$title = $this->m_form->getInputByName('blog-post[title]')->getValue();
				SwissKnife::removeNewLines($title);

			// Post
			$post = $this->m_form->getInputByName('blog-post[post]')->getValue();

			// Permalink
			$permalink = $this->m_form->getInputByName('blog-post[permalink]')->getValue();
			$updatePermalink = false;

			// Get the old permalink
			$q = 'SELECT permalink
				  FROM g_blog
				  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

			$query = $this->m_db->prepare($q);
			$query->execute([
				'id' => $id
			]);

			$reply = $query->fetch();

			$query->closeCursor();

			// Compare it to the new
			if ($permalink != $reply['permalink']) { // Permalink changed

				$updatePermalink = true;

				if (empty($permalink))
					$permalink = $title;

				$permalink = SwissKnife::stringToID($permalink);
				$permalink = $this->makePermalinkUnique($permalink);

				// Update form with new permalink
				$this->m_form->getInputByName('blog-post[permalink]')->setValue($permalink);
			}

			// Update

			$query = null;

			if ($updatePermalink) {

				$q = 'UPDATE g_blog
					  SET permalink=:permalink, title=:title, post=:post, last_edit_date=:last_edit_date
					  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

				$query = $this->m_db->prepare($q);
				$query->execute([
					'permalink' => $permalink,
					'title' => $title,
					'post' => $post,
					'last_edit_date' => date('Y-m-d H:i:s'),
					'id' => $id
				]);

			} else {

				$q = 'UPDATE g_blog
					  SET title=:title, post=:post, last_edit_date=:last_edit_date
					  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

				$query = $this->m_db->prepare($q);
				$query->execute([
					'title' => $title,
					'post' => $post,
					'last_edit_date' => date('Y-m-d H:i:s'),
					'id' => $id
				]);
			}

			$rowsAffected = $query->rowCount();

			$query->closeCursor();

			return $rowsAffected !== 0;
		}

		/**
		 * Delete from DB
		 *
		 * @param $id
		 * @param bool $isPermalink If we read from a permalink
		 * @return bool
		 */
		public function delete($id, $isPermalink = false): bool {

			if ($id === null)
				return false;

			$q = 'DELETE FROM g_blog WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

			$query = $this->m_db->prepare($q);
			$query->execute([
				'id' => $id
			]);

			$rowsAffected = $query->rowCount();

			$query->closeCursor();

			return $rowsAffected !== 0;
		}
	}
