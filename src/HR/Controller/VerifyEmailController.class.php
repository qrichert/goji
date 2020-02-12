<?php

namespace HR\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Core\App;
use Goji\HumanResources\MemberManager;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;

class VerifyEmailController extends ControllerAbstract { // Not cached, displayed email changes

	/* <ATTRIBUTES> */

	private $m_id;
	private $m_token;
	private $m_email;

	public function __construct(App $app) {

		parent::__construct($app);

		// If no ID or token -> error
		if (empty($_GET['id']) || empty($_GET['token']))
			$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);

		$this->m_id = (int) $_GET['id'];
		$this->m_token = (string) $_GET['token'];

		$this->m_email = MemberManager::getTemporaryMemberEmail($this->m_app, $this->m_id, $this->m_token);

		// If email not found (null), so incorrect id/token (token must match id)
		if (empty($this->m_email))
			$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$template = new SimpleTemplate($tr->_('VERIFY_EMAIL_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
									   $tr->_('VERIFY_EMAIL_PAGE_DESCRIPTION'),
									   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

		$template->startBuffer();

		require_once $template->getView('HR/VerifyEmailView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
