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

		protected $m_inPageEditableContent;

		protected $m_requiredRoleForEditing;
		protected $m_defaultLocale;

		protected $m_includeCSS;
		protected $m_styleSheet;

		protected $m_nbContentAreasRendered;

		protected $m_javaScriptLibrary;
		protected $m_baseClass;
		protected $m_editableAreaClass;
		protected $m_editorClass;
		protected $m_buttonsClass;

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

			$this->m_inPageEditableContent = null;

			$this->m_requiredRoleForEditing = 'editor';
			$this->m_defaultLocale = $defaultLocale ?? $this->m_app->getLanguages()->getCurrentLocale();

			$this->m_includeCSS = true;
			$this->m_styleSheet = 'css/lib/Goji/inpagecontentedit.css';

			$this->m_nbContentAreasRendered = 0;

			$this->m_javaScriptLibrary = 'js/lib/Goji/InPageContentEdit-19.11.14.class.min.js';
			$this->m_baseClass = 'in-page-content-edit';
			$this->m_editableAreaClass = $this->m_baseClass . '__editable-area';
			$this->m_editorClass = $this->m_baseClass . '__editor';
			$this->m_buttonsClass = $this->m_baseClass . '__buttons';

			$this->addClass($this->m_baseClass);
			$this->setAttribute('data-action', $this->m_app->getRouter()->getLinkForPage('xhr-admin-in-page-content-edit'));
			$this->setAttribute('data-page-id', $this->m_app->getRouter()->getCurrentPage());
			$this->setAttribute('data-text', htmlspecialchars(json_encode([
				'save_confirm' => $this->m_app->getTranslator()->translate('SAVE_CONFIRM'),
				'cancel_confirm' => $this->m_app->getTranslator()->translate('CANCEL_CONFIRM'),
				'placeholder' => $this->m_app->getTranslator()->translate('SHRUG')
			])));
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
		 * If you don't want to inherit this class just for that, use setInPageEditableContent().
		 *
		 * @param mixed ...$args
		 * @return \Goji\Rendering\InPageEditableContent
		 * @throws \Exception
		 */
		protected function getInPageEditableContent(...$args): InPageEditableContent {

			if ($this->m_inPageEditableContent !== null)
				return new $this->m_inPageEditableContent(...$args);

			return new InPageEditableContent(...$args);
		}

		/**
		 * Set a custom InPageEditableContent (if you don't want to inherit InPageContentEdit)
		 *
		 * You can use YouCustomInPageEditableContent::class to send as parameter for this function.
		 * This way is better because the class namespace won't be hard-written in your code.
		 *
		 * @param string $inPageEditableContent ::class
		 */
		public function setInPageEditableContent(string $inPageEditableContent): void {
			// If wrong class, it will fail at the first getInPageEditableContent()
			$this->m_inPageEditableContent = $inPageEditableContent;
		}

		/**
		 * @param string $contentId
		 * @param string $tagName
		 * @param array $specialClasses
		 * @throws \Exception
		 */
		public function renderContent(string $contentId, string $tagName = 'p', $specialClasses = []) {

			// Make sure it's an array
			if (!is_array($specialClasses))
				$specialClasses = explode(' ', (string) $specialClasses);

			// We don't want to toggle those who are already there, only the 'special/unique ones'
			foreach ($specialClasses as $key => $specialClass) {
				if ($this->hasClass($specialClass))
					unset($specialClasses[$key]);
				else // Add it in the same stint, se we gain a loop
					$this->addClass($specialClass);
			}

			// Render JS if first block
			if ($this->m_nbContentAreasRendered == 0)
				$this->renderJavaScript();

			$this->m_nbContentAreasRendered++;

			// Create editable content model
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

			$rawContent = htmlspecialchars($editableContent->getRawContent());

			$area = <<<EOT
			<div {$this->renderAttributes()} data-content-id="{$contentId}" data-raw-content="{$rawContent}">
				<{$tagName} class="{$this->m_editableAreaClass}">{$formattedContent}</{$tagName}>
				<textarea class="{$this->m_editorClass}"></textarea>
				<div class="{$this->m_buttonsClass}">
					<div class="toolbar">
						<button data-action="save" class="loader highlight">{$this->m_app->getTranslator()->translate('SAVE')}</button>
						<button data-action="preview" class="loader dark">{$this->m_app->getTranslator()->translate('PREVIEW')}</button>
						<button data-action="cancel" class="delete">{$this->m_app->getTranslator()->translate('CANCEL')}</button>
					</div>
				</div>
			</div>
			EOT;

			// Now we can remove the classes which are unique to the current in-page editor
			$this->removeClasses($specialClasses);

			echo $area;
		}
	}
