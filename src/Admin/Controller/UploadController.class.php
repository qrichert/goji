<?php

namespace Admin\Controller;

use Admin\Model\UploadForm;
use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class UploadController extends ControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$uploadForm = new UploadForm($tr, $this->m_app);

		$template = new SimpleTemplate($tr->_('UPLOAD_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
		                               $tr->_('UPLOAD_PAGE_DESCRIPTION'),
		                               SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		require_once $template->getView('Admin/UploadView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
