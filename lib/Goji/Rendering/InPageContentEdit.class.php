<?php

	namespace Goji\Rendering;

	use Goji\Core\App;
	use Goji\Blueprints\HtmlAttributesManagerAbstract;

	/**
	 * Class InPageContentEdit
	 *
	 * @package Goji\Rendering
	 */
	class InPageContentEdit extends HtmlAttributesManagerAbstract {

		/* <ATTRIBUTES> */

		protected $m_app;

		protected $m_defaultLocale;
		protected $m_includeCSS;
		protected $m_styleSheet;
		protected $m_javaScriptLibrary;
		protected $m_baseClass;
		protected $m_editableAreaClass;
		protected $m_editorClass;

		/**
		 * InPageContentEdit constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string|null $defaultLocale
		 * @throws \Exception
		 */
		function __construct(App $app, string $defaultLocale = null) {

			parent::__construct();

			$this->m_app = $app;

			$this->m_defaultLocale = $defaultLocale ?? $this->m_app->getLanguages()->getCurrentLocale();

			$this->m_includeCSS = true;
			$this->m_styleSheet = 'css/lib/Goji/inpagecontentedit.css';
			$this->m_javaScriptLibrary = 'js/lib/Goji/InPageContentEdit-19.11.14.class.js';
			$this->m_baseClass = 'in-page-content-edit';
			$this->m_editableAreaClass = $this->m_baseClass . '__editable-area';
			$this->m_editorClass = $this->m_baseClass . '__editor';

			$this->addClass($this->m_baseClass);
			$this->setAttribute('data-raw-content', '');
			$this->setAttribute('data-action', 'xhr-in-page-content-edit');
			$this->setAttribute('data-page', '');
		}

		/**
		 * @return bool
		 */
		public function getIncludeCSS(): bool {
			return $this->m_includeCSS;
		}

		/**
		 * @param bool $includeCSS
		 */
		public function setIncludeCSS(bool $includeCSS): void {
			$this->m_includeCSS = $includeCSS;
		}

		/**
		 * @return string
		 */
		public function getStyleSheet(): string {
			return $this->m_styleSheet;
		}

		/**
		 * @param string $styleSheet
		 */
		public function setStyleSheet(string $styleSheet): void {
			$this->m_styleSheet = $styleSheet;
		}

		public function renderContent(string $areaId, string $tagName = 'p') {

			$area = new InPageEditableContent($this->m_app, $areaId, $this->m_defaultLocale);

			$area = <<<EOT
			<div {$this->renderAttributes()}>
				<{$tagName} class="{$this->m_editableAreaClass}"></{$tagName}>
				<textarea class="{$this->m_editorClass}"></textarea>
			</div>
			EOT;

			echo $area;
		}
	}
