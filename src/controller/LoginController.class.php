<?php

	namespace App\Controller;

	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Blueprints\ControllerInterface;
	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputTextEmail;
	use Goji\Form\InputTextPassword;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class LoginController implements ControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		private function buildForm(Translator $tr): Form {

			$sanitizeEmail = function($email) {
				$email = mb_strtolower($email);
				return filter_var($email, FILTER_SANITIZE_EMAIL);
			};

			$form = new Form();

				$form->setAttribute('class', 'form__login');

					$form->addInput(new InputLabel())
					     ->setAttribute('for', 'login__email')
					     ->setAttribute('textContent', $tr->_('LOGIN_FORM_EMAIL'));
					$form->addInput(new InputTextEmail(null, false, $sanitizeEmail))
					     ->setAttribute('name', 'login[email]')
					     ->setAttribute('id', 'login__email')
					     ->setAttribute('placeholder', $tr->_('LOGIN_FORM_EMAIL_PLACEHOLDER'))
						 ->setAttribute('required');
					$form->addInput(new InputLabel())
					     ->setAttribute('for', 'login__password')
					     ->setAttribute('textContent', $tr->_('LOGIN_FORM_PASSWORD'))
						 ->setSideInfo('a', array('href' => '#'), $tr->_('LOGIN_FORGOT_PASSWORD'));
					$form->addInput(new InputTextPassword())
						 ->setAttribute('name', 'login[password]')
						 ->setAttribute('id', 'login__password')
						 ->setAttribute('placeholder', $tr->_('LOGIN_FUN_MESSAGE', mt_rand(1, 3)))
						 ->setAttribute('required');
					$form->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
					$form->addInput(new InputButtonElement())
					     ->setAttribute('class', 'highlight loader')
					     ->setAttribute('textContent', $tr->_('LOGIN_FORM_LOG_IN_BUTTON'));

			return $form;
		}

		private function treatForm(Translator $tr, Form &$form): bool {

			$detail = array();
			$isValid = $form->isValid($detail);

			if ($isValid) {

				$this->m_app->getUser()->logIn(1);

				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON(array(
						'email' => $form->getInputByName('login[email]')->getValue(),
						'redirect_to' => $this->m_app->getAuthentication()->getRedirectToOnLogInSuccess()
					), true); // email, redirect_to, add status = SUCCESS
				}

				// Clean the form
				$form = $this->buildForm($tr);

				return true;
			}

			// If AJAX, return JSON
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON(array(
					'detail' => $detail
				), false);
			}

			return false;
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = $this->buildForm($tr);

			$formSentSuccess = null;

			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$form->hydrate();
				$formSentSuccess = $this->treatForm($tr, $form);
			}

			$template = new SimpleTemplate($tr->_('LOGIN_PAGE_TITLE'),
			                               $tr->_('LOGIN_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			require_once '../src/view/login_v.php';

			$template->saveBuffer();

			require_once '../template/page/main_t.php';
		}
	}
