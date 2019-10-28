<?php

	namespace App\Controller\HR;

	use App\Model\HR\SignUpForm;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Core\Logger;
	use Goji\Form\Form;
	use Goji\Security\Passwords;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrSignUpController extends XhrControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): void {

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

					HttpResponse::JSON([
						'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
					], false);
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

				HttpResponse::JSON([
					'message' => $tr->_('SIGN_UP_SUCCESS')
				], true);
			}

			// If we're here, form is not valid (like no login given)

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('SIGN_UP_INVALID_USERNAME')
			], false);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new SignUpForm($tr);
				$form->hydrate();

			$this->treatForm($tr, $form);
		}
	}
