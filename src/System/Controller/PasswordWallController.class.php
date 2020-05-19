<?php

namespace System\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Core\App;
use Goji\Core\Cookies;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Rendering\SimpleTemplate;
use Goji\Translation\Translator;
use System\Model\PasswordWallForm;

class PasswordWallController extends ControllerAbstract { // Not cached, sends responses

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'message' => $tr->_('PASSWORD_WALL_WRONG_PASSWORD'),
				'detail' => $detail
			], false);
		}

		$formPassword = (string) $form->getInputByName('password-wall[password]')->getValue();

		if ($formPassword !== $this->m_app->getPasswordWallPassword()) {

			HttpResponse::JSON([
				'message' => $tr->_('PASSWORD_WALL_WRONG_PASSWORD')
			], false);
		}

		Cookies::set(App::PASSWORD_WALL_COOKIE, $this->m_app->getPasswordWallPassword());

		HttpResponse::JSON([], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new PasswordWallForm($tr, $this->m_app->getRouter()->getLinkForPage(null));

		// If Ajax, treat form and exit, act as a XhrController
		if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

			$form->hydrate();
			$this->treatForm($form);

			exit;
		}

		// Send a 503 Service Unavailable error
		// This is a good option for planned maintenance/downtime
		// http://googlewebmastercentral.blogspot.com/2011/01/how-to-deal-with-planned-site-downtime.html
		HttpResponse::setStatusHeader(self::HTTP_SERVER_SERVICE_UNAVAILABLE);

		$template = new SimpleTemplate($this->m_app->getSiteName(),
		                               $tr->_('PASSWORD_WALL_PAGE_DESCRIPTION'));
			$template->addSpecial('is-minimal-page', true);

		$template->startBuffer();

		require_once $template->getView('System/PasswordWallView');

		$template->saveBuffer();

		require_once $template->getTemplate('page/main');
	}
}
