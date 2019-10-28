<?php

	namespace App\Controller;

	use App\Model\ContactForm;
	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\App;
	use Goji\Core\HttpResponse;
	use Goji\Form\Form;
	use Goji\Toolkit\Mail;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Translation\Translator;

	class XhrContactController extends XhrControllerAbstract {

		private function treatForm(Translator $tr, Form &$form): void {

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

				HttpResponse::JSON([
					'message' => $tr->_('CONTACT_SUCCESS')
				], true);
			}

			HttpResponse::JSON([
				'message' => $tr->_('CONTACT_ERROR'),
				'detail' => $detail
			], false);
		}

		public function render() {

			SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');

			$form = new ContactForm($tr);
				$form->hydrate();

			$this->treatForm($tr, $form);
		}
	}
