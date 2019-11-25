<?php

	namespace App\Controller\HR;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Translation\Translator;

	class VerifyEmailController extends ControllerAbstract {

		public function render(): void {

			// TODO:
			$emailAddress = 'hello@world.com';
			$emailAddress = 'quentin.richert@gmail.com';
			$emailAddress = 'quentin@quentinrichert.com';

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
