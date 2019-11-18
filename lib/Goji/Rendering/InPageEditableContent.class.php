<?php

	namespace Goji\Rendering;

	use Exception;
	use Goji\Blueprints\ModelObjectAbstract;
	use Goji\Core\App;

	/**
	 * Class InPageEditableContent
	 *
	 * @package Goji\Rendering
	 */
	class InPageEditableContent extends ModelObjectAbstract {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_contentId;
		protected $m_pageId;
		protected $m_locale;

		/**
		 * InPageEditableArea constructor.
		 *
		 * This class handles the model part, I/O, for rendering aspect see InPageContentEdit.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $contentId
		 * @param string $pageId
		 * @param string $locale
		 * @throws \Exception
		 */
		public function __construct(App $app, string $contentId, string $pageId, string $locale = null) {

			$this->m_app = $app;

			// Be careful of SQL injections with this one
			$this->m_contentId = $contentId;
				// Only accept A-Z a-z 0-9 _ and -, everything else gets deleted
				$this->m_contentId = preg_replace('#[^a-zA-Z0-9_-]#', '', $this->m_contentId);

			$this->m_pageId = $pageId;
				$this->m_pageId = str_replace("\\", '', $this->m_pageId);
				$this->m_pageId = str_replace("'", '', $this->m_pageId);

			// Those are internal only
			$this->m_locale = $locale ?? $this->m_app->getLanguages()->getCurrentLocale();
				$this->m_locale = str_replace("\\", '', $this->m_locale);
				$this->m_locale = str_replace("'", '', $this->m_locale);

			$whereCondition = <<<EOT
			    content_id='{$this->m_contentId}'
			AND page_id='{$this->m_pageId}'
			AND locale LIKE '{$this->m_locale}%'
			EOT;

			try {

				parent::__construct($app, 'g_editable_content', $whereCondition);

			} catch (Exception $e) {

				// Entry doesn't exist yet
				if ($e->getCode() == self::E_OBJECT_NOT_FOUND_IN_DATABASE) {

					$query = $this->m_app->db()->prepare('INSERT INTO g_editable_content
																( content_id,  page_id,  locale,  content,  last_edit_date,  last_edit_by)
														 VALUES (:content_id, :page_id, :locale, :content, :last_edit_date, :last_edit_by)');

					$query->execute([
						'content_id' => $this->m_contentId,
						'page_id' => $this->m_pageId,
						// Always use full version in database, you can always just fetch en*, etc.
						'locale' => $this->m_app->getLanguages()->getCurrentLocale(),
						'content' => '',
						'last_edit_date' => date('Y-m-d H:i:s'),
						'last_edit_by' => $this->m_app->getUser()->getId()
					]);

					$query->closeCursor();

					parent::__construct($app, 'g_editable_content', $whereCondition);
				}
			}

			$this->writeLock(['id', 'content_id', 'page_id', 'locale']);
		}

		public function updateContent(string $content): void {

			$this->setContent($content);
			$this->setLastEditDate(date('Y-m-d H:i:s'));
			$this->setLastEditBy($this->m_app->getUser()->getId());

			$this->save();
		}

		public static function formatContent($content): string {
			return BasicFormatting::formatTextInlineAndEscape($content);
		}

		public function getRawContent(): string {
			return $this->getContent();
		}

		public function getFormattedContent(): string {
			return $this->formatContent($this->getRawContent());
		}
	}
