<main class="centered">
	<h1 class="hidden" aria-hidden="true"><?= $tr->_('RESET_PASSWORD_MAIN_TITLE'); ?></h1>

	<section class="centered no-padding h">

		<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="form__logo" aria-hidden="true">

		<?php $resetPasswordForm->render(); ?>

		<div>
			<p>
				<?= $tr->_('RESET_PASSWORD_DONE'); ?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>">
					<?= $tr->_('RESET_PASSWORD_LOG_IN'); ?>
				</a>
			</p>
		</div>

	</section>
</main>
