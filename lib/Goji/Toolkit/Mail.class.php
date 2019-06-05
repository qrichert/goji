<?php

	namespace Goji\Toolkit;

	/**
	 * Class Mail
	 *
	 * @package Goji\Toolkit
	 */
	class Mail {

		/**
		 * @param string $to
		 * @param string $subject
		 * @param string $message
		 * @param null $options
		 * @return bool
		 */
		public static function sendMail(string $to, string $subject, string $message, $options = null): bool {

			// Don't forget this is HTML
			// Don't put HTML chars in it without escaping them
			$companySiteURL = $options['site_url'] ?? ''; // https://www.awesomewebsite.com
			$companyName = $options['site_name'] ?? '';
			$companyDomainName = $options['site_domain_name'] ?? ''; // awesomewebsite.com
			//$companyFullDomain = $options['site_full_domain'] ?? ''; // www.awesomewebsite.com
			$companyEmail = $options['company_email'] ?? 'noreply@' . $companyDomainName; // noreply@awesomewebsite.com
			$replyToName = $options['reply_to_name'] ?? $companyName;
			$replyToEmail = $options['reply_to_email'] ?? $companyEmail;
			$templateFile = $options['template_file'] ?? '../template/mail/mail-default_t.html';

			// <CONTENT>
			$emailContent = file_get_contents($templateFile);

				$emailContent = str_replace('%{SUBJECT}', $subject, $emailContent);
				$emailContent = str_replace('%{MESSAGE}', $message, $emailContent);
				$emailContent = str_replace('%{COMPANY_NAME}', $companyName, $emailContent);
				$emailContent = str_replace('%{SITE_URL}', $companySiteURL, $emailContent);
				$emailContent = str_replace('%{DOMAIN_NAME}', $companyDomainName, $emailContent);

			// <HEADERS>
			$headers = 'Content-Type: text/html; charset=UTF-8' . "\n";
			$headers.= 'From: "' . $companyName . '" <' . $companyEmail . '>' . "\n";
			$headers.= 'Reply-To: "' . $replyToName . '" <' . $replyToEmail . '>' . "\n";
			$headers.= 'Content-Transfer-Encoding: 8bit' . "\n\n";

			return mail($to, $subject, $emailContent, $headers);
		}
	}
