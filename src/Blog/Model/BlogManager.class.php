<?php

namespace Blog\Model;

use Goji\Core\App;

class BlogManager {

	/* <ATTRIBUTES> */

	private $m_app;

	public function __construct(App $app) {
		$this->m_app = $app;
	}

	public function setCategories(array $categories): bool {

		\Goji\Debug\Logger::log($categories);

		$currentLocale = $this->m_app->getLanguages()->getCurrentLocale();

		\Goji\Debug\Logger::log($currentLocale);

		// Calculate fingerprint for each category
		foreach ($categories as &$category) {
			$category = [
				'label' => $category,
				'fingerprint' => md5($category),
			];
		}
		unset($category);


		\Goji\Debug\Logger::log($categories);

		return true;
	}
}
