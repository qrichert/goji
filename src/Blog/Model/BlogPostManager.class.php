<?php

namespace Blog\Model;

use Blog\Controller\BlogControllerAbstract;
use Goji\Toolkit\SwissKnife;
use Exception;
use HR\Model\MemberProfile;

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
	 * @param \Blog\Controller\BlogControllerAbstract $parent
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
	 * @return \Blog\Model\BlogPostForm|null
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

		$this->createForm(); // Creates one if none, doesn't overwrite current one if one already exists

		$this->m_form->hydrate();
	}

	/**
	 * @param $id
	 */
	public function hydrateFormWithExistingBlogPost($id): void {

		// If bad ID, read() will fail
		$data = $this->read($id, false, true); // Read raw (e.g. don't transform '%{WEBROOT}')

		$date = SwissKnife::dateToComponents($data['creation_date']);

		$this->createForm(); // Creates one if none, doesn't overwrite current one

			$this->m_form->getInputByName('blog-post[permalink]')->setValue($data['permalink']);
			$this->m_form->getInputByName('blog-post[publication-date][year]')->setValue($date['year']);
			$this->m_form->getInputByName('blog-post[publication-date][month]')->setValue($date['month']);
			$this->m_form->getInputByName('blog-post[publication-date][day]')->setValue($date['day']);
			$this->m_form->getInputByName('blog-post[publication-date][hours]')->setValue($date['hour']);
			$this->m_form->getInputByName('blog-post[publication-date][minutes]')->setValue($date['min']);
			$this->m_form->getInputByName('blog-post[publication-date][seconds]')->setValue($date['sec']);
			$this->m_form->getInputByName('blog-post[illustration]')->setValue($data['illustration']);
			$this->m_form->getInputByName('blog-post[description]')->setValue($data['description']);
			$this->m_form->getInputByName('blog-post[title]')->setValue($data['title']);
			$this->m_form->getInputByName('blog-post[post]')->setValue($data['post']);
	}

// <READ/WRITE>

	/**
	 * @param string $q Query
	 * @param array $params Query parameters
	 * @param callable|null $formatFunction Blog post format function
	 * @param array $formatFunctionParams Blog post format function parameters
	 * @return array
	 */
	protected function fetchBlogPostsFromDatabase(string $q,
	                                              array $params,
	                                              callable $formatFunction = null,
	                                              array $formatFunctionParams = []): array {

		$query = $this->m_db->prepare($q);
		$query->execute($params);

		$reply = $query->fetchAll();

		$query->closeCursor();

		if ($formatFunction !== null) {

			foreach ($reply as &$entry) {
				$entry['post'] = $formatFunction($entry['post'], ...$formatFunctionParams);
			}
			unset($entry);
		}

		foreach ($reply as &$entry) {
			$entry['creation_date'] = SwissKnife::dateToComponents($entry['creation_date']);
			$entry['last_edit_date'] = SwissKnife::dateToComponents($entry['last_edit_date']);
			$entry['hidden'] = SwissKnife::sqlBool($entry['hidden']);
			$entry['created_by_display_name'] = MemberProfile::getDisplayNameForMemberId($this->m_db, $entry['created_by']);
		}
		unset($entry);

		return $reply;
	}

	/**
	 * Returns a list of blog post entries
	 *
	 * @param int $offset
	 * @param int $count -1 = all, infinite
	 * @param string $locale
	 * @param callable|null $formatFunction
	 * @param array $formatFunctionParams
	 * @return array
	 */
	public function getBlogPosts(int $offset = 0,
	                             int $count = -1,
	                             string $locale = null,
	                             callable $formatFunction = null,
	                             array $formatFunctionParams = []): array {

		if ($offset < 0)
			$offset = 0;

		$q = "SELECT *, creation_date > :now AS hidden FROM g_blog ";
		$params = [];

		if (!$this->m_app->getUser()->isLoggedIn()
		    || !$this->m_app->getMemberManager()->memberIs('editor')) {
			$q .= "WHERE creation_date <= :now ";
		} else {
			$q .= 'WHERE 1 ';
		}

		if (!empty($locale)) {
			$q .= 'AND locale LIKE :locale ';
			$params['locale'] = "$locale%";
		}

		$q .= 'ORDER BY creation_date DESC ';

		if ($count > -1) // LIMIT supplied
			$q .= "LIMIT $count OFFSET $offset ";

		$params['now'] = date('Y-m-d H:i:s');

		return $this->fetchBlogPostsFromDatabase($q, $params, $formatFunction, $formatFunctionParams);
	}

	public function getBlogPostsForQuery(string $query,
	                                     int $offset = 0,
	                                     int $count = -1,
	                                     string $locale = null,
	                                     callable $formatFunction = null,
	                                     array $formatFunctionParams = []): array {

		if ($offset < 0)
			$offset = 0;

		// Transforming $query

		// (1) Put all the 'words' into an array
		$queryKeywords = preg_split('#[\W\s_-]#', $query);

		$queryKeywords = array_filter($queryKeywords);

		foreach ($queryKeywords as &$keyword) {

			// (2) Replace plurals and infinitives by SQL wildcards
			if (mb_strlen($keyword) > 4)
				$keyword = preg_replace('#(s|aux|i|e|es|en|er|ir)$#i', '%', $keyword);

			// (3) Remove repeated letters
			$keyword = preg_replace('#(.)\1+#', '$1%', $keyword);

			// (4) Wildcardize keyword
			$keyword = '%' . $keyword . '%';
		}
		unset($keyword);

		// SQLizing query
		$query = [];
		$params = [];

		$i = 0;
		foreach ($queryKeywords as $keyword) {
			$i++;

			$subQuery = [];

			foreach (['title', 'post'] as $tableColumn) {
				$subQuery[] = "$tableColumn LIKE :keyword$i";
			}

			$params["keyword$i"] = $keyword;

			$query[] = '(' . implode(' OR ', $subQuery) . ')';
		}

		$query = implode(' OR ', $query);

		// Getting the posts

		$q = "SELECT *, creation_date > :now AS hidden FROM g_blog WHERE ($query) ";

		if (!$this->m_app->getUser()->isLoggedIn()
		    || !$this->m_app->getMemberManager()->memberIs('editor')) {
			$q .= "AND creation_date <= :now ";
		}

		if (!empty($locale)) {
			$q .= 'AND locale LIKE :locale ';
			$params['locale'] = "$locale%";
		}

		$q .= 'ORDER BY creation_date DESC ';

		if ($count > -1) // LIMIT supplied
			$q .= "LIMIT $count OFFSET $offset ";

		$params['now'] = date('Y-m-d H:i:s');

		return $this->fetchBlogPostsFromDatabase($q, $params, $formatFunction, $formatFunctionParams);
	}

	protected function getSurroundingBlogPost(string $previousOrNext, string $blogPostCreationDate, string $locale = null): ?array {

		$query = null;
		$params = [
			'blog_post_creation_date' => $blogPostCreationDate
		];

		if (!empty($locale)) {
			$params['locale'] = "$locale%";
			$locale = 'AND locale LIKE :locale';
		}

		if ($previousOrNext == 'previous') {

			$query = "SELECT id, permalink, title
					  FROM g_blog
					  WHERE
					        creation_date < :blog_post_creation_date
					    AND creation_date <= :now
					    $locale
					  ORDER BY creation_date DESC
					  LIMIT 1";

		} else if ($previousOrNext == 'next') {

			$query = "SELECT id, permalink, title
					  FROM g_blog
					  WHERE
					        creation_date > :blog_post_creation_date
					    AND creation_date <= :now
				        $locale
					  ORDER BY creation_date ASC
					  LIMIT 1";

		} else {
			return null;
		}

		$params['now'] = date('Y-m-d H:i:s');

		$query = $this->m_db->prepare($query);
		$query->execute($params);
		$reply = $query->fetch();
		$query->closeCursor();

		if ($reply === false || empty($reply))
			return null;

		return $reply;
	}

	public function getPreviousBlogPost(string $blogPostCreationDate, string $locale = null): ?array {
		return $this->getSurroundingBlogPost('previous', $blogPostCreationDate, $locale);
	}

	public function getNextBlogPost(string $blogPostCreationDate, string $locale = null): ?array {
		return $this->getSurroundingBlogPost('next', $blogPostCreationDate, $locale);
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

		// Locale
		$locale = $this->m_translator->getTargetLocale();

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
			$permalink = empty($permalink) ? '-' : $permalink;
			$permalink = $this->makePermalinkUnique($permalink);

			// Update form with new permalink
			$this->m_form->getInputByName('blog-post[permalink]')->setValue($permalink);

		// Date
		$date = [];
		$date['year'] = $this->m_form->getInputByName('blog-post[publication-date][year]')->getValue();
		$date['month'] = $this->m_form->getInputByName('blog-post[publication-date][month]')->getValue();
		$date['day'] = $this->m_form->getInputByName('blog-post[publication-date][day]')->getValue();
		$date['hours'] = $this->m_form->getInputByName('blog-post[publication-date][hours]')->getValue();
		$date['minutes'] = $this->m_form->getInputByName('blog-post[publication-date][minutes]')->getValue();
		$date['seconds'] = $this->m_form->getInputByName('blog-post[publication-date][seconds]')->getValue();

		$date = "{$date['year']}-{$date['month']}-{$date['day']} {$date['hours']}:{$date['minutes']}:{$date['seconds']}";

		// Illustration
		$illustration = $this->m_form->getInputByName('blog-post[illustration]')->getValue();

		// Description
		$description = $this->m_form->getInputByName('blog-post[description]')->getValue();

		// Save
		$query = $this->m_db->prepare('INSERT INTO g_blog
										       ( locale,  permalink,  creation_date,  last_edit_date,  title,  post,  created_by,  illustration,  description)
										VALUES (:locale, :permalink, :creation_date, :last_edit_date, :title, :post, :created_by, :illustration, :description)');

		$query->execute([
			'locale' => $locale,
			'permalink' => $permalink,
			'creation_date' => $date,
			'last_edit_date' => date('Y-m-d H:i:s'),
			'title' => $title,
			'post' => $post,
			'created_by' => $this->m_app->getUser()->getId(),
			'illustration' => $illustration,
			'description' => $description
		]);

		$query->closeCursor();

		return (int) $this->m_db->lastInsertId();
	}

	/**
	 * Read from DB
	 *
	 * @param $id
	 * @param bool $isPermalink If ID is a permalink or a read database id
	 * @param bool $raw
	 * @return array
	 */
	public function read($id, bool $isPermalink = false, bool $raw = false): array {

		if ($id === null)
			$this->m_parent->errorBlogPostDoesNotExist();

		$q = "SELECT *, creation_date > :now AS hidden
			  FROM g_blog
			  WHERE " . ($isPermalink ? 'permalink' : 'id') . "=:id";

		$query = $this->m_db->prepare($q);
		$query->execute([
			'id' => $id,
			'now' => date('Y-m-d H:i:s')
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		if ($reply === false)
			$this->m_parent->errorBlogPostDoesNotExist();

		if (!$raw) {
			$reply['illustration'] = str_replace('%{WEBROOT}', WEBROOT, $reply['illustration']);
			$reply['creation_date'] = SwissKnife::dateToComponents($reply['creation_date']);
			$reply['last_edit_date'] = SwissKnife::dateToComponents($reply['last_edit_date']);
			$reply['hidden'] = SwissKnife::sqlBool($reply['hidden']);
			$reply['created_by_display_name'] = MemberProfile::getDisplayNameForMemberId($this->m_db, $reply['created_by']);
		}

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

		// Permalink - Get the old permalink
		$q = 'SELECT permalink
			  FROM g_blog
			  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

		$query = $this->m_db->prepare($q);
		$query->execute([
			'id' => $id
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		// Permalink -  Compare it to the new
		if ($permalink != $reply['permalink']) { // Permalink changed

			$updatePermalink = true;

			if (empty($permalink))
				$permalink = $title;

			$permalink = SwissKnife::stringToID($permalink);
			$permalink = empty($permalink) ? '-' : $permalink;
			$permalink = $this->makePermalinkUnique($permalink);

			// Update form with new permalink
			$this->m_form->getInputByName('blog-post[permalink]')->setValue($permalink);
		}

		// Date
		$date = [];
		$date['year'] = $this->m_form->getInputByName('blog-post[publication-date][year]')->getValue();
		$date['month'] = $this->m_form->getInputByName('blog-post[publication-date][month]')->getValue();
		$date['day'] = $this->m_form->getInputByName('blog-post[publication-date][day]')->getValue();
		$date['hours'] = $this->m_form->getInputByName('blog-post[publication-date][hours]')->getValue();
		$date['minutes'] = $this->m_form->getInputByName('blog-post[publication-date][minutes]')->getValue();
		$date['seconds'] = $this->m_form->getInputByName('blog-post[publication-date][seconds]')->getValue();

		$date = "{$date['year']}-{$date['month']}-{$date['day']} {$date['hours']}:{$date['minutes']}:{$date['seconds']}";

		// Illustration
		$illustration = $this->m_form->getInputByName('blog-post[illustration]')->getValue();

		// Description
		$description = $this->m_form->getInputByName('blog-post[description]')->getValue();

		// Update
		$query = null;

		if ($updatePermalink) {

			$q = 'UPDATE g_blog
				  SET permalink=:permalink, creation_date=:creation_date, last_edit_date=:last_edit_date, title=:title, post=:post, illustration=:illustration, description=:description
				  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

			$query = $this->m_db->prepare($q);
			$query->execute([
				'permalink' => $permalink,
				'creation_date' => $date,
				'last_edit_date' => date('Y-m-d H:i:s'),
				'title' => $title,
				'post' => $post,
				'illustration' => $illustration,
				'description' => $description,
				'id' => $id
			]);

		} else {

			$q = 'UPDATE g_blog
				  SET creation_date=:creation_date, title=:title, post=:post, illustration=:illustration, description=:description, last_edit_date=:last_edit_date
				  WHERE ' . ($isPermalink ? 'permalink' : 'id') . '=:id';

			$query = $this->m_db->prepare($q);
			$query->execute([
				'creation_date' => $date,
				'title' => $title,
				'post' => $post,
				'illustration' => $illustration,
				'description' => $description,
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
