<?php

	namespace App\Controller\HR;

	use App\Model\HR\ResetPasswordForm;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\Logger;
	use Goji\Form\Form;
	use Goji\Security\Passwords;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrResetPasswordController extends XhrControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): void {

			$detail = [];

			if (!$form->isValid($detail)) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('RESET_PASSWORD_ERROR')
				], false);
			}

			// Verify validity here (credentials validity)

			// User input
			$formUsername = $form->getInputByName('reset-password[email]')->getValue();

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

			if ($reply <= 0) { // User doesn't exist

				// If error return negative JSON response

				HttpResponse::JSON([
					'message' => $tr->_('RESET_PASSWORD_ERROR')
				], false);
			}

			// If we got here, credentials are valid -> SUCCESS -> reset password

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
			// Users
			$query = $this->m_app->db()->prepare('UPDATE g_user
													SET password=:password
													WHERE username=:username');

			$query->execute([
				'username' => $formUsername,
				'password' => $hashedPassword
			]);

			// And tmp Users
			$query = $this->m_app->db()->prepare('UPDATE g_user_tmp
													SET password=:password
													WHERE username=:username');

			$query->execute([
				'username' => $formUsername,
				'password' => $hashedPassword
			]);

			$query->closeCursor();

			// Send Mail
			$message = $tr->_('RESET_PASSWORD_EMAIL_MESSAGE');
				$message = str_replace('%{PASSWORD}', htmlspecialchars($newPassword), $message);

			$options = [
				'site_url' => $this->m_app->getSiteUrl(),
				'site_name' => $this->m_app->getSiteName(),
				'site_domain_name' => $this->m_app->getSiteDomainName(),
				'company_email' => $this->m_app->getCompanyEmail()
			];

			Mail::sendMail($this->m_app->getCompanyEmail(), $tr->_('RESET_PASSWORD_EMAIL_OBJECT'), $message, $options, $this->m_app->getAppMode() === App::DEBUG);

			HttpResponse::JSON([
				'message' => $tr->_('RESET_PASSWORD_SUCCESS')
			], true);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new ResetPasswordForm($tr);
				$form->hydrate();

			$this->treatForm($tr, $form);
		}
	}
