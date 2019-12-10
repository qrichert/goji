<?php

	namespace Admin\Controller;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Rendering\InPageEditableContent;

	class XhrInPageContentEditController extends XhrControllerAbstract {

		public function render(): void {

			$action = $_POST['action'] ?? null;
			$contentId = $_POST['content-id'] ?? null;
			$pageId = $_POST['page-id'] ?? null;
			$locale = $this->m_app->getLanguages()->getCurrentCountryCode();
			$content = (string) $_POST['content'] ?? '';

			if (empty($action) || empty($contentId) || empty($pageId)) {
				HttpResponse::JSON([], false);
			}

			if ($action == 'get-formatted-content') {

				HttpResponse::JSON([
					'content' => InPageEditableContent::formatContent($content)
				], true);

			} else if ($action == 'save-content') {

				// string $contentId, string $pageId, string $locale = null
				$editableContent = new InPageEditableContent($this->m_app, $contentId, $pageId, $locale);

				$editableContent->updateContent($content);

				HttpResponse::JSON([
					'content' => $editableContent->getFormattedContent()
				], true);
			}
		}
	}
