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

		protected $m_requiredRoleForEditing;
		protected $m_defaultLocale;

		protected $m_includeCSS;
		protected $m_styleSheet;

		protected $m_nbContentAreasRendered;

		protected $m_javaScriptLibrary;
		protected $m_baseClass;
		protected $m_editableAreaClass;
		protected $m_editorClass;

		/**
		 * InPageContentEdit constructor.
		 *
		 * This class handles the View aspect of it. For content, see InPageEditableContent.
		 *
		 * @param \Goji\Core\App $app
		 * @param string|null $defaultLocale
		 * @throws \Exception
		 */
		function __construct(App $app, string $defaultLocale = null) {

			parent::__construct();

			$this->m_app = $app;

			$this->m_requiredRoleForEditing = 'editor';
			$this->m_defaultLocale = $defaultLocale ?? $this->m_app->getLanguages()->getCurrentLocale();

			$this->m_includeCSS = true;
			$this->m_styleSheet = 'css/lib/Goji/inpagecontentedit.css';

			$this->m_nbContentAreasRendered = 0;

			$this->m_javaScriptLibrary = 'js/lib/Goji/InPageContentEdit-19.11.14.class.js';
			$this->m_baseClass = 'in-page-content-edit';
			$this->m_editableAreaClass = $this->m_baseClass . '__editable-area';
			$this->m_editorClass = $this->m_baseClass . '__editor';

			$this->addClass($this->m_baseClass);
			$this->setAttribute('data-action', 'xhr-in-page-content-edit');
			$this->setAttribute('data-page-id', $this->m_app->getRouter()->getCurrentPage());
			$this->setAttribute('data-placeholder', '¯\_(ツ)_/¯');
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

		protected function userCanEdit(): bool {
			return ($this->m_app->getUser()->isLoggedIn()
			        && $this->m_app->getMemberManager()->memberIs($this->m_requiredRoleForEditing));
		}

		protected function renderJavaScript(): void {

			if (!$this->userCanEdit())
				return;

			echo <<<EOT
			<script src="{$this->m_javaScriptLibrary}"></script>
			<script>
				(function() {
					window.addEventListener('load', () => {
						document.querySelectorAll('.in-page-content-edit').forEach(el => new InPageContentEdit(el));
					}, false);
				})();
			</script>
			EOT;
		}

		private function renderAttributesNoEdit(): string {

			$attr = '';

			$dontRender = [
				'class',
				'data-action',
				'data-page-id',
				'data-placeholder',
			];

			// Remove base (edit) class
			$classes = $this->getClasses();
				$index = array_search($this->m_baseClass, $classes);
				if ($index !== false)
					array_splice($classes, $index, 1);

			// Render classes
			if (!empty($classes))
				$attr .= 'class="' . implode(' ', $classes) . '"';

			// Render other attributes
			foreach ($this->m_attributes as $key => $value) {

				if (in_array($key, $dontRender))
					continue;

				if (!empty($value))
					$attr .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
				else
					$attr .= ' ' . $key;
			}

			return trim($attr);
		}

		/**
		 * For inheritance, so no need to override whole methods.
		 *
		 * @param mixed ...$args
		 * @return \Goji\Rendering\InPageEditableContent
		 * @throws \Exception
		 */
		protected function getInPageEditableContent(...$args): InPageEditableContent {
			return new InPageEditableContent(...$args);
		}

		public function renderContent(string $contentId, string $tagName = 'p') {

			if ($this->m_nbContentAreasRendered == 0)
				$this->renderJavaScript();

			$this->m_nbContentAreasRendered++;

			$editableContent = $this->getInPageEditableContent($this->m_app,
			                                     $contentId,
			                                     $this->m_app->getRouter()->getCurrentPage(),
			                                     $this->m_defaultLocale);


			$formattedContent = $editableContent->getFormattedContent();

			if (!$this->userCanEdit()) {

				$area = <<<EOT
				<div {$this->renderAttributesNoEdit()}>
					<{$tagName}>{$formattedContent}</{$tagName}>
				</div>
				EOT;

				echo $area;

				return;
			}

			$rawContent = addcslashes($editableContent->getRawContent(), '"');

			$area = <<<EOT
			<div {$this->renderAttributes()} data-content-id="{$contentId}" data-raw-content="{$rawContent}">
				<{$tagName} class="{$this->m_editableAreaClass}">{$formattedContent}</{$tagName}>
				<textarea class="{$this->m_editorClass}"></textarea>
			</div>
			EOT;

			echo $area;
		}
	}
