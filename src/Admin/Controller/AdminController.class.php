<?php

namespace Admin\Controller;

use Admin\Model\AddMemberForm;
use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class AdminController extends ControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$addMemberForm = new AddMemberForm($tr, $this->m_app);

		$terminalPath = null;

			if (is_dir('_terminal'))
				$terminalPath = '_terminal';
			else if (is_dir('terminal'))
				$terminalPath = 'terminal';

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
