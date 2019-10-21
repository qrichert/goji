<?php

	namespace App\Controller\HR;

	use App\Model\HR\LoginForm;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Form\Form;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Security\Passwords;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class ResetPasswordController extends ControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): bool { // TODO: this class and page

			$detail = [];
			$isValid = $form->isValid($detail);

			if ($isValid) { // Form is valid, in the sense that required info is there, email is an email, etc.

				// Verify validity here (credentials validity)

				// User input
				$formUsername = $form->getInputByName('login[email]')->getValue();
				$formPassword = $form->getInputByName('login[password]')->getValue();

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

				// If error return false or negative JSON response if Ajax
				if ($reply === false || empty($userId) || empty($userPassword)
				    || !Passwords::verifyPassword($formPassword, $userPassword)) {

					// If AJAX, return JSON
					if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

						HttpResponse::JSON([
							'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
						], false);
					}

					return false;
				}

				// If we got here, credentials are valid -> SUCCESS -> log the user in

				$this->m_app->getUser()->logIn((int) $userId);

				// If AJAX, return JSON (SUCCESS)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'email' => $form->getInputByName('login[email]')->getValue(),
						'redirect_to' => $this->m_app->getAuthentication()->getRedirectToOnLogInSuccess()
					], true); // email, redirect_to, add status = SUCCESS
				}

				// If not Ajax...

				// Clean the form
				$form = new LoginForm($tr);

				return true;
			}

			// If we're here, form is not valid (like no login or password given)

			// If AJAX, return JSON (ERROR)
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON([
					'detail' => $detail,
					'message' => $tr->_('LOGIN_WRONG_USERNAME_OR_PASSWORD')
				], false);
			}

			// We don't clean the form in this case, so the user can correct without retyping everything

			return false;
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new LoginForm($tr);

			$formSentSuccess = null;

			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$form->hydrate();
				$formSentSuccess = $this->treatForm($tr, $form);
			}

			$template = new SimpleTemplate($tr->_('LOGIN_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('LOGIN_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once $template->getView('LoginView');

			$template->saveBuffer();

			require_once $template->getTemplate('page/main');
		}
	}
