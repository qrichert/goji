<?php

	namespace App\Controller;

	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Blueprints\ControllerInterface;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\Form\InputButtonElement;
	use Goji\Form\InputCustom;
	use Goji\Form\InputLabel;
	use Goji\Form\InputText;
	use Goji\Form\InputTextEmail;
	use Goji\Form\InputTextArea;
	use Goji\Toolkit\Mail;
	use Goji\Translation\Translator;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\SimpleTemplate;

	class ContactController implements ControllerInterface {

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

				$form->setAttribute('class', 'form__contact');

					$form->addInput(new InputLabel())
						 ->setAttribute('for', 'contact__name')
						 ->setAttribute('textContent', $tr->_('CONTACT_FORM_NAME'));
					$form->addInput(new InputText())
						 ->setAttribute('name', 'contact[name]')
						 ->setAttribute('id', 'contact__name')
						 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_NAME_PLACEHOLDER'));
					$form->addInput(new InputLabel())
						 ->setAttribute('for', 'contact__email')
						 ->setAttribute('textContent', $tr->_('CONTACT_FORM_EMAIL'));
					$form->addInput(new InputTextEmail(null, false, $sanitizeEmail))
						 ->setAttribute('name', 'contact[email]')
						 ->setAttribute('id', 'contact__email')
						 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_EMAIL_PLACEHOLDER'));
					$form->addInput(new InputLabel())
						 ->setAttribute('for', 'contact__message')
						 ->setAttribute('class', 'required')
						 ->setAttribute('textContent', $tr->_('CONTACT_FORM_MESSAGE'));
					$form->addInput(new InputTextArea())
						 ->setAttribute('name', 'contact[message]')
						 ->setAttribute('id', 'contact__message')
						 ->setAttribute('class', 'big')
						 ->setAttribute('placeholder', $tr->_('CONTACT_FORM_MESSAGE_PLACEHOLDER'))
						 ->setAttribute('required');
					$form->addInput(new InputCustom('<div class="progress-bar"><div class="progress"></div></div>'));
					$form->addInput(new InputButtonElement())
						 ->setAttribute('class', 'highlight loader')
						 ->setAttribute('textContent', $tr->_('SEND'));

			return $form;
		}

		private function treatForm(Translator $tr, Form &$form): bool {

			$detail = array();
			$isValid = $form->isValid($detail);

			if ($isValid) {

				$name = $form->getInputByName('contact[name]')->getValue();
				$email = $form->getInputByName('contact[email]')->getValue();
				$message = $form->getInputByName('contact[message]')->getValue();
					$message = htmlspecialchars(nl2br($message));

				$message = <<<EOT
<strong>From:</strong> $name &lt;$email&gt;<br>
<br>
<strong>Message:</strong><br>
<br>
$message
EOT;

				$options = array(
					'site_url' => $this->m_app->getSiteUrl(),
					'site_name' => $this->m_app->getSiteName(),
					'site_domain_name' => $this->m_app->getSiteDomainName(),
					'company_email' => $this->m_app->getCompanyEmail()
				);

				Mail::sendMail($this->m_app->getCompanyEmail(), 'New message from contact form', $message, $options);

				// If AJAX, return JSON
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON(array(
						'message' => $tr->_('CONTACT_SUCCESS')
					), true);
				}

				// Clean the form
				$form = $this->buildForm($tr);

				return true;
			}

			// If AJAX, return JSON
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON(array(
					'message' => $tr->_('CONTACT_ERROR'),
					'detail' => $detail
				), false);
			}

			return false;
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			// Translation
			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			// Form
			$form = $this->buildForm($tr);

			$formSentSuccess = null;

			if ($this->m_app->getRequestHandler()->getRequestMethod() == HttpMethodInterface::HTTP_POST) {

				$form->hydrate();
				$formSentSuccess = $this->treatForm($tr, $form);
			}

			// Template
			$template = new SimpleTemplate($tr->_('CONTACT_PAGE_TITLE'),
										   $tr->_('CONTACT_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once '../src/view/contact_v.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main_t.php';
		}
	}
