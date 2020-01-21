<?php

namespace Admin\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class AdminController extends ControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$template = new SimpleTemplate($tr->_('ADMIN_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
		                               $tr->_('ADMIN_PAGE_DESCRIPTION'),
		                               SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		require_once $template->getView('Admin/AdminView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
