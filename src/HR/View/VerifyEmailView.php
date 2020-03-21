<main class="centered">
	<section class="text">
		<h1 class="hidden" aria-hidden="true"><?= $tr->_('VERIFY_EMAIL_MAIN_TITLE'); ?></h1>
		<h2><?= $tr->_('VERIFY_EMAIL_CHECK_YOUR_INBOX'); ?></h2>
	</section>

	<section class="centered">
		<p>
			<?php
			$txt = str_replace('%{EMAIL}',
		                $this->m_email,
		                $tr->_('VERIFY_EMAIL_INSTRUCTIONS'));

			$txt = str_replace('%{LOGIN}',
						$this->m_app->getRouter()->getLinkForPage('login'),
						$txt);

			echo $txt;
			?>
		</p>

		<img src="<?= $template->rsc('img/lib/Goji/notification.svg'); ?>" alt="" class="verify-email__notification-logo">

		<p class="verify-email__resend-verification">
			<?= str_replace('%{ID}',
			                'verify-email__resend-verification-button',
			                $tr->_('VERIFY_EMAIL_RESEND_VERIFICATION')); ?>
		</p>
	</section>
</main>

<script>
	(function() {

		let resendVerification = document.querySelector('#verify-email__resend-verification-button');

		let resendVerificationText = resendVerification.textContent;

		let data = new FormData();
			data.append('id', '<?= addcslashes($this->m_id, "'"); ?>');
			data.append('token', '<?= addcslashes($this->m_token, "'"); ?>');

		resendVerification.addEventListener('click', e => {

			e.preventDefault();

			let error = () => {
			};

			let load = (r, s) => {

				if (r === null || s !== 200) {
					error();
					return;
				}

				// If we're here -> SUCCESS
				resendVerification.textContent = resendVerificationText +
				                                 ' ' + '<?= addcslashes($tr->_('VERIFY_EMAIL_RESEND_VERIFICATION_DONE'), "'"); ?>';
			};

			SimpleRequest.post(
				'<?= $this->m_app->getRouter()->getLinkForPage('xhr-verify-email-not-received'); ?>',
				data,
				load,
				error,
				error,
				null,
				{ get_json: true }
			);

			resendVerification.textContent = resendVerificationText + '...';

		}, false);

	})();
</script>
