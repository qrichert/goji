<?php

namespace Redirection\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Core\App;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

/**
 * Class RedirectionControllerAbstract
 *
 * We want to do a JavaScript redirection here, so we can track the redirection
 * with external tracking software like Google Analytics.
 *
 * @package Redirection\Controller
 */
class RedirectionControllerAbstract extends ControllerAbstract {

	private $m_redirectTo;

	public function __construct(App $app) {
		parent::__construct($app);
		$this->m_redirectTo = '';
	}

	protected function getRedirectTo(): string {
		return $this->m_redirectTo;
	}

	protected function setRedirectTo(string $redirectTo): void {
		$this->m_redirectTo = $redirectTo;
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
		$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$template = new SimpleTemplate($tr->_('REDIRECTION_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
			$tr->_('REDIRECTION_PAGE_DESCRIPTION'),
			SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);
			$template->addSpecial('is-funnel-page', true);
			$template->addSpecial('is-minimal-page', true);

		$template->startBuffer();

		require_once $template->getView('Redirection/RedirectionView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
