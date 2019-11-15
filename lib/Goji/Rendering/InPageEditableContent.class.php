<?php

	namespace Goji\Rendering;

	use Goji\Core\App;

	/**
	 * Class InPageEditableContent
	 *
	 * @package Goji\Rendering
	 */
	class InPageEditableContent {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_db;
		protected $m_areaId;
		protected $m_pageId;
		protected $m_locale;

		/**
		 * InPageEditableArea constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $areaId
		 * @param string $pageId
		 * @param string $locale
		 * @throws \Exception
		 */
		public function __construct(App $app, string $areaId, string $pageId,string $locale) {

			$this->m_app = $app;
			$this->m_db = $this->m_app->db();

			$this->m_areaId = $areaId;
			$this->m_pageId = $pageId;
			$this->m_locale = $locale;
		}
	}
