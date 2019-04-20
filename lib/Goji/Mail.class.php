<?php

	namespace Goji;

	class Mail {

		public static function sendMail($to, $subject, $message, $senderEmail = null, $replyToName = null, $replyToEmail = null) {

			// Don't forget this is HTML
			// Don't put HTML chars in it without escaping them
			$COMPANY_NAME			= SITE_NAME; // AwesomeWebsite
			$COMPANY_LOGO			= SITE_URL . '/img/logo-mail.png'; // Logo image
			$COMPANY_EMAIL			= isset($senderEmail) ? $senderEmail : ('support@' . SITE_DOMAIN); // || 'support@awesomewebsite.com''
			$COMPANY_DOMAIN_NAME	= SITE_DOMAIN_FULL; // www.awesomewebsite.com
			$COMPANY_WEBSITE_URL	= SITE_URL; // https://www.awesomewebsite.com

			if ($replyToName === null)
				$replyToName = $COMPANY_NAME;

			if ($replyToEmail === null)
				$replyToEmail = $COMPANY_EMAIL;

			// <CONTENT>

			$emailContent = file_get_contents('../template/mail/mail-default_t.html');

				$emailContent = str_replace('%{SUBJECT}', $subject, $emailContent);
				$emailContent = str_replace('%{MESSAGE}', $message, $emailContent);
				$emailContent = str_replace('%{COMPANY_NAME}', $COMPANY_NAME, $emailContent);
				$emailContent = str_replace('%{COMPANY_LOGO}', $COMPANY_LOGO, $emailContent);
				$emailContent = str_replace('%{DOMAIN_NAME}', $COMPANY_DOMAIN_NAME, $emailContent);
				$emailContent = str_replace('%{WEBSITE_URL}', $COMPANY_WEBSITE_URL, $emailContent);

			// </CONTENT>

			$headers = 'Content-Type: text/html; charset=UTF-8' . "\n";
			$headers.= 'From: "' . $COMPANY_NAME . '" <' . $COMPANY_EMAIL . '>' . "\n";
			$headers.= 'Reply-To: "' . $replyToName . '" <' . $replyToEmail . '>' . "\n";
			$headers.= 'Content-Transfer-Encoding: 8bit' . "\n\n";

			return mail($to, $subject, $emailContent, $headers);
		}
	}
