<?php

	namespace Goji\Rendering;

	use Exception;
	use Goji\Blueprints\ModelObjectAbstract;
	use Goji\Core\App;
	use Goji\Core\Logger;

	/**
	 * Class InPageEditableContent
	 *
	 * @package Goji\Rendering
	 */
	class InPageEditableContent extends ModelObjectAbstract {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_contentId;
		protected $m_locale;
		protected $m_countryCode;
		protected $m_pageId;

		/**
		 * InPageEditableArea constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $contentId
		 * @param string $locale
		 * @param string $pageId
		 * @throws \Exception
		 */
		public function __construct(App $app, string $contentId, string $locale = null, string $pageId = null) {

			$this->m_app = $app;

			// Be careful of SQL injections with this one
			$this->m_contentId = $contentId;
				// Only accept A-Z a-z 0-9 _ and -, everything else gets deleted
				$this->m_contentId = preg_replace('#[^a-zA-Z0-9_-]#', '', $this->m_contentId);

			// Those are internal only
			$this->m_locale = $locale ?? $this->m_app->getLanguages()->getCurrentLocale();
				$this->m_locale = str_replace("\\", '', $this->m_locale);
				$this->m_locale = str_replace("'", '', $this->m_locale);

			$this->m_pageId = $pageId ?? $this->m_app->getRouter()->getCurrentPage();
				$this->m_pageId = str_replace("\\", '', $this->m_pageId);
				$this->m_pageId = str_replace("'", '', $this->m_pageId);

			$whereCondition = <<<EOT
			    content_id='{$this->m_contentId}'
			AND page_id='{$this->m_pageId}'
			AND locale LIKE '{$this->m_locale}%'
			EOT;

			Logger::log($whereCondition, Logger::CONSOLE);

			try {

				parent::__construct($app, 'g_editable_content', $whereCondition);

			} catch (Exception $e) {

				// Entry doesn't exist yet
				if ($e->getCode() == self::E_OBJECT_NOT_FOUND_IN_DATABASE) {

					$query = $this->m_app->db()->prepare('INSERT INTO g_editable_content
																( content_id,  page_id,  locale)
														 VALUES (:content_id, :page_id, :locale)');

					$query->execute([
						'content_id' => $this->m_contentId,
						'page_id' => $this->m_pageId,
						// Always use full version in database, you can always just fetch en*, etc.
						'locale' => $this->m_app->getLanguages()->getCurrentLocale(),
					]);

					$query->closeCursor();

					parent::__construct($app, 'g_editable_content', $whereCondition);
				}
			}

			$this->writeLock(['id', 'content_id', 'page_id', 'locale']);
		}
	}
