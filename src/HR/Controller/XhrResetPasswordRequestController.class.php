<?php

namespace HR\Controller;

use Exception;
use Goji\Core\App;
use Goji\Core\HttpResponse;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Form\Form;
use Goji\HumanResources\MemberManager;
use Goji\Toolkit\Mail;
use Goji\Translation\Translator;
use HR\Model\ResetPasswordRequestForm;

class XhrResetPasswordRequestController extends XhrControllerAbstract {

	private function treatForm(Form $form): void {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('RESET_PASSWORD_RQ_ERROR')
			], false);
		}

		// Verify validity here (credentials validity)

		// User input
		$formUsername = $form->getInputByName('reset-password-request[email]')->getValue();

		$token = '';

		try {

			$token = MemberManager::queueResetPasswordRequest($this->m_app, $formUsername);

		} catch (Exception $e) {

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_RQ_ERROR')
			], false);
		}

		$link = $this->m_app->getRouter()->getLinkForPage('reset-password', null, true);
			$link .= '?token=' . rawurlencode($token);

		// Send Mail
		$message = $tr->_('RESET_PASSWORD_RQ_EMAIL_MESSAGE');
			$message = str_replace('%{LINK}', htmlspecialchars($link), $message);

		$options = [
			'site_url' => $this->m_app->getSiteUrl(),
			'site_name' => $this->m_app->getSiteName(),
			'site_domain_name' => $this->m_app->getSiteDomainName(),
			'company_email' => $this->m_app->getCompanyEmail()
		];

		Mail::sendMail($formUsername, $tr->_('RESET_PASSWORD_RQ_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

		HttpResponse::JSON([
			'message' => $tr->_('RESET_PASSWORD_RQ_SUCCESS')
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new ResetPasswordRequestForm($tr);
			$form->hydrate();

		$this->treatForm($form);
	}
}
