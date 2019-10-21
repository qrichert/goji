<?php

	namespace App\Controller\HR;

	use App\Model\HR\SignUpForm;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\Logger;
	use Goji\Form\Form;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Security\Passwords;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class SignUpController extends ControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): bool {

			$detail = [];
			$isValid = $form->isValid($detail);

			if ($isValid) { // Form is valid, in the sense that required info is there, email is an email, etc.

				// Verify validity here (credentials validity)

				// User input
				$formUsername = $form->getInputByName('sign-up[email]')->getValue();

				// Database
				$query = $this->m_app->db()->prepare('SELECT
																	(SELECT COUNT(*)
																	FROM g_user
																	WHERE username=:username)
																+
																	(SELECT COUNT(*)
																	FROM g_user_tmp
																	WHERE username=:username)
																AS nb');

				$query->execute([
					'username' => $formUsername
				]);

				$reply = $query->fetch();
					$reply = (int) $reply['nb'];

				$query->closeCursor();

				if ($reply !== 0) { // User already exists

					// If AJAX, return JSON
					if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

						HttpResponse::JSON([
							'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
						], false);
					}

					return false;
				}

				// Generate Password
				$newPassword = Passwords::generatePassword(7);
				$hashedPassword = Passwords::hashPassword($newPassword);

				/*********************/

				if ($this->m_app->getAppMode() === App::DEBUG) {
					// Log generated password to console
					Logger::log('Email: ' . $formUsername, Logger::CONSOLE);
					Logger::log('Password: ' . $newPassword, Logger::CONSOLE);
				}

				/*********************/

				// Save to DB
				$query = $this->m_app->db()->prepare('INSERT INTO g_user_tmp
															   ( username,  password,  date_registered)
														VALUES (:username, :password, :date_registered)');

				$query->execute([
					'username' => $formUsername,
					'password' => $hashedPassword,
					'date_registered' => date('Y-m-d H:i:s')
				]);

				$query->closeCursor();

				// Send Mail
				$message = $tr->_('SIGN_UP_EMAIL_MESSAGE');
					$message = str_replace('%{PASSWORD}', htmlspecialchars($newPassword), $message);

				$options = [
					'site_url' => $this->m_app->getSiteUrl(),
					'site_name' => $this->m_app->getSiteName(),
					'site_domain_name' => $this->m_app->getSiteDomainName(),
					'company_email' => $this->m_app->getCompanyEmail()
				];

				Mail::sendMail($this->m_app->getCompanyEmail(), $tr->_('SIGN_UP_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

				// If AJAX, return JSON (SUCCESS)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'message' => $tr->_('SIGN_UP_SUCCESS')
					], true);
				}

				// If not Ajax...

				// Clean the form
				$form = new SignUpForm($tr);

				return true;
			}

			// If we're here, form is not valid (like no login given)

			// If AJAX, return JSON (ERROR)
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
				], false);
			}

			// We don't clean the form in this case, so the user can correct without retyping everything

			return false;
		}

		public function render() {

			// TODO: Impletement "didn't receive my email with the password"

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new SignUpForm($tr);

			$formSentSuccess = null;

			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$form->hydrate();
				$formSentSuccess = $this->treatForm($tr, $form);
			}

			$template = new SimpleTemplate($tr->_('SIGN_UP_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('SIGN_UP_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('HR/SignUpView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
