<main class="centered">
	<section class="text">
		<h1 class="hidden" aria-hidden="true"><?= $tr->_('VERIFY_EMAIL_MAIN_TITLE'); ?></h1>
		<h2><?= $tr->_('VERIFY_EMAIL_CHECK_YOUR_INBOX'); ?></h2>
	</section>

	<section class="centered">
		<p>
			<?php
				$txt = str_replace('%{EMAIL}',
			                $emailAddress,
			                $tr->_('VERIFY_EMAIL_INSTRUCTIONS'));

				$txt = str_replace('%{LOGIN}',
							$this->m_app->getRouter()->getLinkForPage('login'),
							$txt);

				echo $txt;
			?>
		</p>

		<img style="width: 170px;" src="" alt="">

		<p class="verify-email__resend-verification">
			<?= str_replace('%{ID}',
			                'verify-email__resend-verification-button',
			                $tr->_('VERIFY_EMAIL_RESEND_VERIFICATION')); ?>
		</p>
	</section>
</main>

<script>
	(function () {

		let resendVerification = document.querySelector('#verify-email__resend-verification-button');

		let data = new FormData();
			data.append('id', '<?= addcslashes($_GET['id'], "'"); ?>');
			data.append('token', '<?= addcslashes($_GET['token'], "'"); ?>');

		resendVerification.addEventListener('click', e => {

			e.preventDefault();

			alert(data);

		}, false);

	})();
</script>
