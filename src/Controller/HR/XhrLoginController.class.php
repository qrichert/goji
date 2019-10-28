<?php

	namespace App\Controller\HR;

	use App\Model\HR\LoginForm;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\Security\Passwords;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrLoginController extends XhrControllerAbstract {

		private function handleTmpUser($username, $password): void {

			$query = $this->m_app->db()->prepare('SELECT *
															FROM g_user_tmp
															WHERE username=:username');

			$query->execute([
				'username' => $username
			]);

			$reply = $query->fetch();

			$query->closeCursor();

			// Not tmp user, quit
			if ($reply === false)
				return;

			// It is a tmp user, check password
			if (empty($password) || !Passwords::verifyPassword($password, $reply['password'])) // Invalid password
				return; // Just quit, log in will fail anyway

			// User is valid, move him to the real list
			$query = $this->m_app->db()->prepare('INSERT INTO g_user
															   ( username,  password,  date_registered)
														VALUES (:username, :password, :date_registered)');

			$query->execute([
				'username' => $reply['username'],
				'password' => $reply['password'],
				'date_registered' => $reply['date_registered']
			]);

			// And delete tmp entry
			$query = $this->m_app->db()->prepare('DELETE FROM g_user_tmp
															WHERE id=:id OR username=:username');

			$query->execute([
				'id' => $reply['id'],
				'username' => $reply['username']
			]);

			$query->closeCursor();
		}

		private function treatForm(Translator $tr, Form &$form): void {

			$detail = [];
			$isValid = $form->isValid($detail);

			if ($isValid) { // Form is valid, in the sense that required info is there, email is an email, etc.

				// Verify validity here (credentials validity)

				// User input
				$formUsername = $form->getInputByName('login[email]')->getValue();
				$formPassword = $form->getInputByName('login[password]')->getValue();

				// If temp user & valid -> transfer him to the real user list
				$this->handleTmpUser($formUsername, $formPassword);

				// Database
				$query = $this->m_app->db()->prepare('SELECT id, password
																FROM g_user
																WHERE username=:username');
				$query->execute([
					'username' => $formUsername
				]);

				$reply = $query->fetch();

				$query->closeCursor();

				// Stored values
				$userId = $reply['id'] ?? null;
				$userPassword = $reply['password'] ?? null;

				// If error return negative JSON response
				if ($reply === false || empty($userId) || empty($userPassword)
				    || !Passwords::verifyPassword($formPassword, $userPassword)) {

					HttpResponse::JSON([
						'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
					], false);
				}

				// If we got here, credentials are valid -> SUCCESS -> log the user in

				$this->m_app->getUser()->logIn((int) $userId);

				HttpResponse::JSON([
					'email' => $form->getInputByName('login[email]')->getValue(),
					'redirect_to' => $this->m_app->getAuthentication()->getRedirectToOnLogInSuccess()
				], true); // email, redirect_to, add status = SUCCESS
			}

			// If we're here, form is not valid (like no login or password given)

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
			], false);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new LoginForm($tr);
				$form->hydrate();

			$this->treatForm($tr, $form);
		}
	}

