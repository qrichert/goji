<?php

namespace Blog\Model;

use Goji\Core\App;

class BlogManager {

	/* <ATTRIBUTES> */

	private $m_app;

	public function __construct(App $app) {
		$this->m_app = $app;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getCategories(): array {

		$query = $this->m_app->getDatabase()->prepare('SELECT id, name
														FROM g_blog_category
														WHERE locale=:locale
														ORDER BY name');

		$query->execute([
			'locale' => $this->m_app->getLanguages()->getCurrentLocale()
		]);

		$reply = $query->fetchAll();

		$query->closeCursor();

		return $reply;
	}

	/**
	 * @param array $categories
	 * @return bool
	 * @throws \Exception
	 */
	public function setCategories(array $categories): bool {

	// 1: Sanitation

		foreach ($categories as &$category) {
			// Make ID integer or null if none (new category)
			$category['id'] = !empty($category['id']) ? (int) $category['id'] : null;
			// Clean category name
			$category['name'] = (string) $category['name'];
			$category['name'] = trim($category['name']);
			$category['name'] = preg_replace(\Goji\Parsing\RegexPatterns::whiteSpace(), ' ', $category['name']);
		}
		unset($category);

	// 2: Delete categories that no longer exist in new list

		// $ids = array_column($categories, 'id'); --> No, we want to sanitize it first
		$ids = [];

		foreach ($categories as $category) {
			if (!empty($category['id']))
				$ids[] = $category['id'];
		}

		$query = 'DELETE FROM g_blog_category
					WHERE locale=:locale ';

		// If $ids empty, just delete all of them
		if (!empty($ids))
			$query .= 'AND id NOT IN (' . implode(', ', $ids) . ')';

		$query = $this->m_app->getDatabase()->prepare($query);

		$query->execute([
			'locale' => $this->m_app->getLanguages()->getCurrentLocale()
		]);

		$query->closeCursor();

	// 3: Delete links between categories and blog posts when category doesn't exist
		$this->m_app->getDatabase()->exec('DELETE FROM g_blog_category_post
														WHERE category_id NOT IN (
														    SELECT id
														    FROM g_blog_category
														)');

		foreach ($categories as $category) {

			if (empty($category['name']))
				continue;

	// 4.a Already exists in DB -> UPDATE
			if (!empty($category['id'])) {

				$query = $this->m_app->getDatabase()->prepare('UPDATE g_blog_category
																SET name=:name
																WHERE id=:id');

				$query->execute([
					'name' => $category['name'],
					'id' => $category['id']
				]);

				$query->closeCursor();

	// 4.b Doesn't exist in DB -> INSERT
			} else {

				$query = $this->m_app->getDatabase()->prepare('INSERT INTO g_blog_category
																		( id,  locale,  name)
																 VALUES (:id, :locale, :name)');

				$query->execute([
					'id' => $category['id'],
					'locale' => $this->m_app->getLanguages()->getCurrentLocale(),
					'name' => $category['name'],
				]);

				$query->closeCursor();
			}
		}

		return true;
	}
}
