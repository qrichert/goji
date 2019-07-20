<?php

	namespace App\Controller;

	use App\Model\ContactForm;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Core\App;
	use Goji\Blueprints\ControllerInterface;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
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

		private function treatForm(Translator $tr, Form &$form): bool {

			$detail = [];
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

				$options = [
					'site_url' => $this->m_app->getSiteUrl(),
					'site_name' => $this->m_app->getSiteName(),
					'site_domain_name' => $this->m_app->getSiteDomainName(),
					'company_email' => $this->m_app->getCompanyEmail()
				];

				Mail::sendMail($this->m_app->getCompanyEmail(), 'New message from contact form', $message, $options);

				// If AJAX, return JSON (SUCCESS)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'message' => $tr->_('CONTACT_SUCCESS')
					], true);
				}

				// Clean the form
				$form = new ContactForm($tr);

				return true;
			}

			// If AJAX, return JSON (ERROR)
			if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

				HttpResponse::JSON([
					'message' => $tr->_('CONTACT_ERROR'),
					'detail' => $detail
				], false);
			}

			return false;
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			// Translation
			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			// Form
			$form = new ContactForm($tr);

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
			require_once '../src/View/ContactView.php';

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once '../template/page/main.template.php';
		}
	}
