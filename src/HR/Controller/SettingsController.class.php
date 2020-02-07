<?php

namespace HR\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;
use HR\Model\SettingsPasswordForm;

class SettingsController extends ControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');
			$tr->loadTranslationResource('%{LOCALE}.tr.xml', false, 'xhr-reset-password-request');

		$settingsPasswordForm = new SettingsPasswordForm($tr);

		$template = new SimpleTemplate($tr->_('SETTINGS_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
									   $tr->_('SETTINGS_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-focus-page', true);

		$template->startBuffer();

		require_once $template->getView('HR/SettingsView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
