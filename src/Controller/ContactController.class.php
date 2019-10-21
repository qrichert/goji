<?php

	namespace App\Controller;

	use App\Model\ContactForm;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\Rendering\SimpleTemplate;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class ContactController extends ControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): bool {

			$detail = [];
			$isValid = $form->isValid($detail);

			if ($isValid) {

				$name = $form->getInputByName('contact[name]')->getValue();
				$email = $form->getInputByName('contact[email]')->getValue();
				$message = $form->getInputByName('contact[message]')->getValue();
					$message = nl2br(htmlspecialchars($message));

				$message = <<<EOT
					<p>
						<strong>From:</strong> $name &lt;$email&gt;<br>
					</p>
					<p>
						<strong>Message:</strong><br>
						<br>
						$message
					</p>
					EOT;

				$options = [
					'site_url' => $this->m_app->getSiteUrl(),
					'site_name' => $this->m_app->getSiteName(),
					'site_domain_name' => $this->m_app->getSiteDomainName(),
					'company_email' => $this->m_app->getCompanyEmail()
				];

				Mail::sendMail($this->m_app->getCompanyEmail(), 'New message from contact form', $message, $options, $this->m_app->getAppMode() === App::DEBUG);

				// If AJAX, return JSON (SUCCESS)
				if ($this->m_app->getRequestHandler()->isAjaxRequest()) {

					HttpResponse::JSON([
						'message' => $tr->_('CONTACT_SUCCESS')
					], true);

				} else {
					// Clean the form
					$form = new ContactForm($tr);
				}

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
			$template = new SimpleTemplate($tr->_('CONTACT_PAGE_TITLE') . ' - ' . $this->m_app->getSiteName(),
										   $tr->_('CONTACT_PAGE_DESCRIPTION'),
										   SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW);

			$template->startBuffer();

			// Getting the view (into buffer)
			require_once $template->getView('ContactView');

			// Now the view is accessible as string w/ $template->getPageContent()
			$template->saveBuffer();

			// Inside the template file we call $template to put things in place.
			require_once $template->getTemplate('page/main');
		}
	}
